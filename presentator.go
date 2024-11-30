package presentator

import (
	_ "github.com/presentator/presentator/v3/migrations"

	"github.com/pocketbase/pocketbase"
)

// @todo remove after adding support for custom Admin UI settings fields.
const (
	OptionFooterLinks      string = "pr_footerLinks"
	OptionTermsURL         string = "pr_termsURL"
	OptionAllowHotspotsURL string = "pr_allowHotspotsURL"
)

type Presentator struct {
	*pocketbase.PocketBase
}

func New() *Presentator {
	pr := &Presentator{pocketbase.New()}

	// default options
	pr.Store().Set(OptionAllowHotspotsURL, true)

	bindAppHooks(pr)

	return pr
}
