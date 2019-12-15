// Client-Side Validation Functions

function validateUsername(field) {
	if (field == "") return "No username was entered.\n"
	else if (field.length < 5)
		return "Usernames must be at least 5 characters.\n"
	else if (/[^a-zA-Z0-9_-]/.test(field))
		return "Only a-z, A-Z, 0-9, - and _ allowed in usernames.\n"
	return ""
}

function validatePassword(field) {
	if (field == "") return "No password was entered.\n"
	else if (field.length < 6)
		return "Passwords must be at least 6 characters.\n"
	else if (!/[a-z]/.test(field) || !/[A-Z]/.test(field) ||!/[0-9]/.test(field))
		return "Passwords require one each of a-z, A-Z and 0-9.\n"
	return ""
}

function validateEmail(field) {
	if (field == "") return "No email was entered.\n"
	else if (!((field.indexOf(".") > 0) && (field.indexOf("@") > 0)) || /[^a-zA-Z0-9.@_-]/.test(field))
		return "The email address is invalid.\n"
	return ""
}