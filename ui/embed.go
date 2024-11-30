package ui

import (
	"embed"

	"github.com/pocketbase/pocketbase/apis"
)

//go:embed all:dist
var distDir embed.FS

// DistDirFS contains the embedded dist directory files (without the "dist" prefix)
var DistDirFS = apis.MustSubFS(distDir, "dist")
