package presentator

import (
	_ "github.com/presentator/presentator/v3/migrations"

	"github.com/pocketbase/pocketbase"
)

// @todo remove after adding support for custom Admin UI settings fields.
const (
	OptionFooterLinks      string = "pr_footerLinks"
	OptionTermsUrl         string = "pr_termsUrl"
	OptionAllowHotspotsUrl string = "pr_allowHotspotsUrl"
)

type Presentator struct {
	*pocketbase.PocketBase
}

func New() *Presentator {
	pr := &Presentator{pocketbase.New()}

	// default options
	pr.Store().Set(OptionAllowHotspotsUrl, true)

	bindAppHooks(pr)

	return pr
}
