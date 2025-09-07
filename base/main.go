package main

import (
	"log"
	"os"
	"path/filepath"
	"strings"

	"github.com/pocketbase/pocketbase/plugins/ghupdate"
	"github.com/pocketbase/pocketbase/plugins/jsvm"
	"github.com/pocketbase/pocketbase/plugins/migratecmd"
	"github.com/pocketbase/pocketbase/tools/osutils"
	"github.com/presentator/presentator/v3"
)

func main() {
	app := presentator.New()

	// ---------------------------------------------------------------
	// Optional flags:
	// ---------------------------------------------------------------

	var hooksDir string
	app.RootCmd.PersistentFlags().StringVar(
		&hooksDir,
		"hooksDir",
		"",
		"the directory with the JS app hooks",
	)

	var hooksWatch bool
	app.RootCmd.PersistentFlags().BoolVar(
		&hooksWatch,
		"hooksWatch",
		true,
		"auto restart the app on pb_hooks file change",
	)

	var hooksPool int
	app.RootCmd.PersistentFlags().IntVar(
		&hooksPool,
		"hooksPool",
		25,
		"the total prewarm goja.Runtime instances for the JS app hooks execution",
	)

	var migrationsDir string
	if osutils.IsProbablyGoRun() {
		migrationsDir = filepath.Join(app.DataDir(), "../migrations")
	}
	app.RootCmd.PersistentFlags().StringVar(
		&migrationsDir,
		"migrationsDir",
		migrationsDir,
		"the directory with the user defined migrations (default to pb_data/../pb_migrations)",
	)

	var allowHotspotsUrl bool
	app.RootCmd.PersistentFlags().BoolVar(
		&allowHotspotsUrl,
		"allowHotspotsUrl",
		true,
		"allow or disallow the hotspot url type to prevent abuse as in issue#147",
	)

	var footerLinks []string
	app.RootCmd.PersistentFlags().StringSliceVar(
		&footerLinks,
		"footerLinks",
		nil,
		"comma separated footer links in the format 'title1|url1, title2|url2, ...'\nexample: 'Privacy policy|https://example.com/policy,Contacts|https://example.com/contacts'",
	)

	var termsUrl []string
	app.RootCmd.PersistentFlags().StringSliceVar(
		&termsUrl,
		"termsUrl",
		nil,
		"URL to your Terms and Conditions page that is referenced during users registration",
	)

	app.RootCmd.ParseFlags(os.Args[1:])

	// update app options
	app.Store().Set(presentator.OptionFooterLinks, arrayLinksToMap(footerLinks))
	app.Store().Set(presentator.OptionAllowHotspotsURL, allowHotspotsUrl)
	app.Store().Set(presentator.OptionTermsURL, termsUrl)

	// ---------------------------------------------------------------
	// Plugins and hooks:
	// ---------------------------------------------------------------

	// load jsvm (hooks and migrations)
	jsvm.MustRegister(app, jsvm.Config{
		MigrationsDir: migrationsDir,
		HooksDir:      hooksDir,
		HooksWatch:    hooksWatch,
		HooksPoolSize: hooksPool,
	})

	// migrate command
	// use Go templates with "go run", otherwise JS
	migrateConfig := migratecmd.Config{Dir: migrationsDir}
	if !osutils.IsProbablyGoRun() {
		migrateConfig.TemplateLang = migratecmd.TemplateLangJS
	}
	migratecmd.MustRegister(app, app.RootCmd, migrateConfig)

	// GitHub selfupdate
	ghupdate.MustRegister(app, app.RootCmd, ghupdate.Config{
		Owner:             "presentator",
		Repo:              "presentator",
		ArchiveExecutable: "presentator",
	})

	if err := app.Start(); err != nil {
		log.Fatal(err)
	}
}

// converts ["title1|url1", "title2|url2"] into {title1:url1, title2:url2}
func arrayLinksToMap(links []string) map[string]string {
	result := map[string]string{}

	for _, l := range links {
		parts := strings.SplitN(l, "|", 2)
		if len(parts) != 2 {
			continue
		}
		result[parts[0]] = parts[1]
	}

	return result
}
