variable "tinybird_token" {  
  type				= string
  sensitive		= true
  nullable		= false
	description = <<-EOT
	Токен для Tinybird, базы данных, с полным доступом
	https://tinybird.co/docs/get-started/administration/auth-tokens#create-a-token
	EOT
}

variable "yandex_cloud_id" {
  type        = string
  sensitive   = false
	nullable		= false
	description	= <<-EOT
	Идентификатор облака Yandex Cloud, где будет находится каталог с ботом
	https://yandex.cloud/docs/resource-manager/operations/cloud/get-id
	EOT
}

variable "yandex_folder_id" {
  type        = string
  sensitive   = false
	nullable		= false
	description	= <<-EOT
	Идентификатор каталога Yandex Cloud, где будет развёрнут бот
	https://yandex.cloud/docs/resource-manager/operations/folder/get-id
	EOT
}

variable "yandex_token" {
  type        = string
  sensitive   = true
	nullable		= false
	description	= <<-EOT
	Токен OAuth для Yandex Cloud
	https://yandex.cloud/docs/iam/concepts/authorization/oauth-token
	EOT
}

variable "telegram_admins_ids" {
  type        = list(number)
	sensitive 	= false
	nullable		= false
	default 		= ["987595197"]
	description	= <<-EOT
	ID администраторов бота в Telegram
	Свой и чужой ID можно узнать через https://t.me/myidbot
	EOT
}

variable "telegram_token" {
  type        = string
  sensitive		= true
	nullable		= false
	description	= <<-EOT
	Токен бота в Telegram
	https://core.telegram.org/bots/tutorial#obtain-your-bot-token
	EOT
}

variable "businessru_account_id" {
  type        = string
  sensitive 	= false
  nullable		= false
	description	= <<-EOT
	Идентификатор склада из URL в Бизнес.Ру
	Находится на его странице: https://[ЗДЕСЬ].beta-class365.ru/
	EOT
}

variable "businessru_app_id" {
	type				= number
  sensitive		= false
  nullable		= false
	description	= <<-EOT
	ID интеграции в Бизнес.Ру
	Получается при создании "Интеграции по API" в магазине приложений
	EOT
}

variable "businessru_app_secret" {
  type				= string
  sensitive 	= true
	nullable 		= false 
	description	= <<-EOT
	Секретный ключ интеграции в Бизнес.Ру
	Получается при создании "Интеграции по API" в магазине приложений
	EOT
}