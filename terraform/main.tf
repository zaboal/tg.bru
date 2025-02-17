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
  token     = var.yc_token
  cloud_id  = var.yc_cloud
  folder_id = var.yc_folder
  zone      = "ru-central1-a"
}

resource "random_id" "user_hash" {
  byte_length = 16
}

data "archive_file" "function_zip1" {
  type        = "zip"
  source_dir  = "${path.module}/../src/functional"
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
    API_KEY        = var.tg_token
    ADMINS         = join(",", var.tg_admins)
    ACCOUNT        = var.bru_account
    SECRET         = var.bru_token
    APP_ID         = var.bru_id
    TOKEN		   = var.tb_token
    TOKEN2		   = var.tb_token
  }
}

resource "null_resource" "trigger_webhook" {
  depends_on = [yandex_function.my_function1]

  provisioner "local-exec" {
    command = "curl -X POST \"https://api.telegram.org/bot${var.tg_token}/setWebhook?url=https://functions.yandexcloud.net/${yandex_function.my_function1.id}\""
  }
}

data "archive_file" "function_zip2" {
  type        = "zip"
  source_dir  = "${path.module}/../src/bru"
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
    API_KEY = var.tg_token
    TOKEN = var.tb_token
  }
}