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

data "archive_file" "function_zip1" {
  type        = "zip"
  source_dir  = "${path.module}/src/functional"
  output_path = "${path.module}/archive.zip"
}

resource "yandex_function" "my_function1" {
  name              = "terraform-function1"
  description       = "For tg"
  user_hash         = random_id.user_hash.hex
  runtime           = "php82"
  entrypoint        = "index.handler"
  memory            = 128
  execution_timeout = 10

  content {
    zip_filename = data.archive_file.function_zip1.output_path
  }

  environment = {
    API_KEY        = var.api_key
    ADMINS         = var.admins
    ACCOUNT        = var.account
    SECRET         = var.secret
    APP_ID         = var.app_id
    TOKEN		   = var.tinybird_token
    TOKEN2		   = var.tinybird_token2
  }
}

resource "null_resource" "trigger_webhook" {
  depends_on = [yandex_function.my_function1]

  provisioner "local-exec" {
    command = "curl -X POST \"https://api.telegram.org/bot${var.api_key}/setWebhook?url=https://functions.yandexcloud.net/${yandex_function.my_function1.id}\""
  }
}

resource "null_resource" "trigger_bru" {
}

output "function_https_url" {
  value = yandex_function.my_function1.id
}


data "archive_file" "function_zip2" {
  type        = "zip"
  source_dir  = "${path.module}/src/bru"
  output_path = "${path.module}/archive2.zip"
}

resource "yandex_function" "my_function2" {
  name              = "terraform-function2"
  description       = "For tg2"
  user_hash         = random_id.user_hash.hex
  runtime           = "php82"
  entrypoint        = "index.handler"
  memory            = 128
  execution_timeout = 10

  content {
    zip_filename = data.archive_file.function_zip2.output_path
  }

  environment = {
    API_KEY = var.api_key
    TOKEN = var.tinybird_token
  }
}

output "function_https_url2" {
  value = yandex_function.my_function2.id
}
