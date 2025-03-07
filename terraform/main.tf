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
    curl = {
      source  = "marcofranssen/curl"
      version = "0.7.0"
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

data "archive_file" "telegram_function" {
  type        = "zip"
  source_dir  = "${path.module}/../src/functions/telegram"
  output_path = "${path.module}/tg.yc-func.zip"
}

resource "yandex_function" "telegram" {
  depends_on        = [data.archive_file.telegram_function]
  name              = "telegram"
  description       = "Вебхук для Telegram-бота"
  user_hash         = filebase64sha256(data.archive_file.telegram_function.output_path)
  runtime           = "php82"
  entrypoint        = "index.handler"
  memory            = 128
  execution_timeout = 10

  content {
    zip_filename = data.archive_file.telegram_function.output_path
  }

  environment = {
    API_KEY = var.telegram_token
    ADMINS  = join(",", var.telegram_admins_ids)
    ACCOUNT = var.businessru_account_id
    SECRET  = var.businessru_app_secret
    APP_ID  = var.businessru_app_id
    TOKEN   = var.tinybird_token
    TOKEN2  = var.tinybird_token
  }
}

data "curl_request" "telegram_webhook" {
  depends_on  = [yandex_function.telegram]
  uri         = "https://api.telegram.org/bot${var.telegram_token}/setWebhook?url=https://functions.yandexcloud.net/${yandex_function.telegram.id}"
  http_method = "POST"
}

data "curl_request" "telegram_getme" {
  uri         = "https://api.telegram.org/bot${var.telegram_token}/getMe"
  http_method = "GET"
}

data "archive_file" "businessru_function" {
  type        = "zip"
  source_dir  = "${path.module}/../src/functions/businessru"
  output_path = "${path.module}/bru.yc-func.zip"
}

resource "yandex_function" "businessru" {
  depends_on        = [data.archive_file.businessru_function]
  name              = "businessru"
  description       = "Вебхук для Бизнес.Ру"
  user_hash         = filebase64sha256(data.archive_file.businessru_function.output_path)
  runtime           = "php82"
  entrypoint        = "index.handler"
  memory            = 128
  execution_timeout = 10

  content {
    zip_filename = data.archive_file.businessru_function.output_path
  }

  environment = {
    API_KEY = var.telegram_token
    TOKEN   = var.tinybird_token
  }
}

data "curl_request" "businessru_token" {
  depends_on  = [yandex_function.businessru]
  uri         = "https://${var.businessru_account_id}.business.ru/api/rest/repair.json?app_id=${var.businessru_app_id}&app_psw=${md5(join("", [var.businessru_app_secret, "app_id=", var.businessru_app_id]))}"
  http_method = "GET"
}

data "curl_request" "businessru_webhook" {
  depends_on  = [yandex_function.businessru]
  uri         = "https://${var.businessru_account_id}.business.ru/api/rest/webhookurl.json?app_id=${var.businessru_app_id}&url=https://functions.yandexcloud.net/${yandex_function.businessru.id}&app_psw=${md5(join("", [jsondecode(data.curl_request.businessru_token.response_body).token, var.businessru_app_secret, "app_id=", var.businessru_app_id, "&url=", urlencode(join("", ["https://functions.yandexcloud.net/", yandex_function.businessru.id]))]))}"
  http_method = "PUT"
}

data "curl_request" "businessru_webhook_discountcards" {
  depends_on  = [yandex_function.businessru]
  uri         = "https://${var.businessru_account_id}.business.ru/api/rest/webhooks.json?app_id=${var.businessru_app_id}&webhook_model_id=42&app_psw=${md5(join("", [jsondecode(data.curl_request.businessru_token.response_body).token, var.businessru_app_secret, "app_id=", var.businessru_app_id, "&webhook_model_id=42"]))}"
  http_method = "POST"
}