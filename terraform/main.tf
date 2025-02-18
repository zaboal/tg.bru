terraform {
  required_providers {
    yandex = {
      source  = "yandex-cloud/yandex"
      version = ">= 0.136.0"
    }
    archive = {
      source  = "hashicorp/archive"
      version = ">= 2.0.0"
    }
  }
  required_version = ">= 0.13"
}

provider "yandex" {
  token     = var.yandex_token
  cloud_id  = var.yandex_cloud_id
  folder_id = var.yandex_folder_id
  zone      = "ru-central1-a"
}

resource "random_id" "user_hash" {
  byte_length = 16
}

data "archive_file" "telegram_function" {
  type        = "zip"
  source_dir  = "${path.module}/../src/functional"
  output_path = "${path.module}/tg.yc-func.zip"
}

resource "yandex_function" "telegram" {
  depends_on        = [data.archive_file.telegram_function]
  name              = "telegram"
  description       = "Вебхук для Telegram-бота"
  user_hash         = random_id.user_hash.hex
  runtime           = "php82"
  entrypoint        = "index.handler"
  memory            = 128
  execution_timeout = 10

  content {
    zip_filename = data.archive_file.telegram_function.output_path
  }

  environment = {
    API_KEY        = var.telegram_token
    ADMINS         = join(",", var.telegram_admins_ids)
    ACCOUNT        = var.businessru_account_id
    SECRET         = var.businessru_token
    APP_ID         = var.businessru_app_id
    TOKEN		   = var.tinybird_token
    TOKEN2		   = var.tinybird_token
  }
}

resource "null_resource" "telegram_setup_webhook" {
  depends_on = [yandex_function.telegram]

  provisioner "local-exec" {
    command = "curl -X POST \"https://api.telegram.org/bot${var.telegram_token}/setWebhook?url=https://functions.yandexcloud.net/${yandex_function.telegram.id}\""
  }
}

data "archive_file" "businessru_function" {
  type        = "zip"
  source_dir  = "${path.module}/../src/bru"
  output_path = "${path.module}/bru.yc-func.zip"
}

resource "yandex_function" "businessru" {
  depends_on        = [ data.archive_file.businessru_function ]
  name              = "businessru"
  description       = "Вебхук для Бизнес.Ру"
  user_hash         = random_id.user_hash.hex
  runtime           = "php82"
  entrypoint        = "index.handler"
  memory            = 128
  execution_timeout = 10

  content {
    zip_filename = data.archive_file.businessru_function.output_path
  }

  environment = {
    API_KEY = var.telegram_token
    TOKEN = var.tinybird_token
  }
}