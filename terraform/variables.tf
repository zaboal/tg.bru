variable "yandex_token" {
  description = "Yandex Cloud OAuth token"
  type        = string
  sensitive   = true
}

variable "yandex_cloud_id" {
  description = "Yandex Cloud ID"
  type        = string
  sensitive   = true
}

variable "yandex_folder_id" {
  description = "Yandex Cloud Folder ID"
  type        = string
  sensitive   = true
}

variable "api_key" {
  description = "API key для Telegram"
  type        = string
  sensitive = true
}

variable "admins" {
  description = "Список администраторов (в виде строки, разделённой запятыми)"
  type        = string
}

variable "account" {
  description = "Account для business_ru"
  type        = string
  sensitive = true
}

variable "secret" {
  description = "Secret для business_ru"
  type        = string
  sensitive = true
}

variable "app_id" {
  description = "APP_ID для business_ru"
  type        = string
  sensitive = true
}

variable "tinybird_token" {
  description = "Token для tinybird"
  type        = string
  sensitive = true
}

variable "tinybird_token2" {
  description = "Token для tinybird2"
  type        = string
  sensitive = true
}
