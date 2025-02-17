variable "tb_token" {  
  type				= string
  sensitive		= true
  nullable		= false
	description = <<-EOT
	Токен для Tinybird, базы данных, с полным доступом
	https://tinybird.co/docs/get-started/administration/auth-tokens#create-a-token
	EOT
}

variable "yc_cloud" {
  type        = string
  sensitive   = false
	nullable		= false
	description	= <<-EOT
	Идентификатор облака Yandex Cloud, где будет находится каталог с ботом
	https://yandex.cloud/docs/resource-manager/operations/cloud/get-id
	EOT
}

variable "yc_folder" {
  type        = string
  sensitive   = false
	nullable		= false
	description	= <<-EOT
	Идентификатор каталога Yandex Cloud, где будет развёрнут бот
	https://yandex.cloud/docs/resource-manager/operations/folder/get-id
	EOT
}

variable "yc_token" {
  type        = string
  sensitive   = true
	nullable		= false
	description	= <<-EOT
	Токен OAuth для Yandex Cloud
	https://yandex.cloud/docs/iam/concepts/authorization/oauth-token
	EOT
}

variable "tg_admins" {
  type        = list(string)
	sensitive 	= false
	nullable		= false
	default 		= ["987595197"]
	description	= <<-EOT
	ID администраторов бота в Telegram
	Свой и чужой ID можно узнать через https://t.me/myidbot
	EOT
}

variable "tg_token" {
  type        = string
  sensitive		= true
	nullable		= false
	description	= <<-EOT
	Токен бота в Telegram
	https://core.telegram.org/bots/tutorial#obtain-your-bot-token
	EOT
}

variable "bru_account" {
  type        = string
  sensitive 	= false
  nullable		= false
	description	= <<-EOT
	Идентификатор склада из URL в Бизнес.Ру
	Находится на его странице: https://[ЗДЕСЬ].beta-class365.ru/
	EOT
}

variable "bru_id" {
	type				= string
  sensitive		= false
  nullable		= false
	description	= <<-EOT
	ID интеграции в Бизнес.Ру
	Получается при создании "Интеграции по API" в магазине приложений
	EOT
}

variable "bru_token" {
  type				= string
  sensitive 	= true
	nullable 		= false 
	description	= <<-EOT
	Секретный ключ интеграции в Бизнес.Ру
	Получается при создании "Интеграции по API" в магазине приложений
	EOT
}