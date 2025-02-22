output "telegram_url" {
  value       = "https://t.me/${nonsensitive(jsondecode(data.curl_request.telegram_getme.response_body).result.username)}"
  description = <<-EOT
  Ссылка на бота в Telegram
  https://core.telegram.org/api/links#public-username-links
  EOT 
}

output "businessru_url" {
  value       = "https://${var.businessru_account_id}.business.ru"
  description = <<-EOT
  Ссылка на склад в Бизнес.Ру
  EOT
}