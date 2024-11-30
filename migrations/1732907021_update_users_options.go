package migrations

import (
	"github.com/pocketbase/pocketbase/core"
	m "github.com/pocketbase/pocketbase/migrations"
)

func init() {
	m.Register(func(app core.App) error {
		usersCollection, err := app.FindCollectionByNameOrId("users")
		if err != nil {
			return err
		}

		usersCollection.VerificationTemplate.Body = "<p>Hello,</p>\n<p>Thank you for joining us at {APP_NAME}.</p>\n<p>Click on the button below to verify and activate your account.</p>\n<p style=\"text-align: center\">\n    <a class=\"btn\" href=\"{APP_URL}/#/confirm-verification/{TOKEN}\">Activate account</a>\n</p>"
		usersCollection.ResetPasswordTemplate.Body = "<p>Hello,</p>\n<p>Click on the button below to reset your password.</p>\n<p style=\"text-align: center\">\n    <a class=\"btn\" href=\"{APP_URL}/#/confirm-password-reset/{TOKEN}\">Reset password</a>\n</p>\n<p>If you didn't request to reset your password, you can ignore this email and the link will expire on its own.</p>"
		usersCollection.ConfirmEmailChangeTemplate.Body = "<p>Hello,</p>\n<p>We've received a request to change your {APP_NAME} account email from {Record:email}.</p>\n<p>Click on the button below to confirm your new email address.</p>\n<p style=\"text-align: center\">\n    <a class=\"btn\" href=\"{APP_URL}/#/confirm-email-change/{TOKEN}\">Confirm new email</a>\n</p>\n<p>If you didn't request this email, please ignore it and the link will expire on its own.</p>"
		usersCollection.OTP.EmailTemplate.Body = "<p>Hello,</p>\n<p>Your one-time password is:</p>\n<center class=\"section\">\n    <p class=\"break-spaces\"><strong>{OTP}</strong></p>\n</center>\n<p>If you didn't ask for the one-time password, you can ignore this email and it will expire on its own.</p>"
		usersCollection.AuthAlert.EmailTemplate.Body = "<p>Hello,</p>\n<p>We noticed a login to your {APP_NAME} account from a new location.</p>\n<p>If this was you, you may disregard this email.</p>\n<p><strong>If this wasn't you, you should immediately change your {APP_NAME} account password to revoke access from all other locations.</strong></p>"
		usersCollection.AuthAlert.Enabled = false

		return app.Save(usersCollection)
	}, nil)
}
