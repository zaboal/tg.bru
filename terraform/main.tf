resource "random_pet" "id" {
  length = 2
}

module "telegram_function" {
  source = "github.com/zaboal/tf-yc.git?ref=adopt-terraform-aws-lambda"

  name = "telegram"
  description = "Вебхук для Telegram-бота"
  
  source_path = "${path.module}/../src/functions/telegram"
  runtime = "php82"
  entrypoint = "index.handler"
  
  lockbox_secret_value = null
  lockbox_secret_key = null
  
  choosing_trigger_type = "message_queue"
  
  scaling_policy = [{
    tag = null
    zone_instances_limit = null
    zone_requests_limit = null
  }]
  
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
  depends_on  = [ module.telegram_function ]
  uri         = "https://api.telegram.org/bot${var.telegram_token}/setWebhook?url=https://functions.yandexcloud.net/${module.telegram_function.function_id}"
  http_method = "POST"
}

data "curl_request" "telegram_getme" {
  depends_on  = [ module.telegram_function ]
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
  user_hash         = tostring(random_pet.id)
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