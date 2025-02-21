<h1> Бизнес.Ру для Telegram </h1>

<a href="//t.me/bru_tg"><img src="https://img.shields.io/badge/группа-поддержка-FFFFFF?style=flat-square&logo=telegram&logoColor=white" alt="Группа в Telegram"/></a>
<a href="//t.me/bstilbot"><img src="https://img.shields.io/badge/бот-рабочий_пример-FFFFFF?style=flat-square&logo=telegram&logoColor=white" alt="Группа в Telegram"/></a>

<p> 
Telegram-бот на основе PHP, Yandex Cloud + Terraform, и Tinybird, для взаимодействия с 
<a href="//online.business.ru/vozmozhnosti/sistema-loyalnosti">системой лояльности склада</a> 
на Бизнес.Ру (Класс365) 
путём <a href="//api-online.class365.ru/api-polnoe/podklyuchenie_chastnoj_integratsii/3811"><q>частной интеграции</q></a>.
</p>

<p> На <a href="//github.com/zaboal/tg-bru/commits/main/README.md">момент обновления этого <code>README</code></a> реализовано:
<ul>
    <li>получение собственных бонусов по телефону, если будет найдена дисконтная карта с соотвествующим номером;</li>
    <li>запоминание этого номера, и уведомление об изменениях баланса;</li>
    <li>возможность для администраторов узнать чужое кол-во бонусов.</li>
</ul>
</p>

<h2 id="install"> Установка </h2>

<ol>
    <li>Установите Terraform <a href="https://developer.hashicorp.com/terraform/install">(официальная инструкция)</a>,</li>
    <li>будьте зарегестрированы во всех указанных в <a href="/terraform/variables.tf"><code>/terraform/variables.tf</code></a> сервисах и заполучите токены,</li>
    <li>разверните сервис: <pre><code>terraform -chdir=terraform apply</code></pre></li>
</ol>

<h2 id="license"> Лицензия </h2>

<p>
Бот лицензирован под <b>AGPL v3</b>, 
его текст в <a href="LICENSE"><code>LICENSE</code></a>. 
Частные случаи: 
<ul>
    <li>запускаете и/или продаёте такую услугу — укажите ссылку на этот репозиторий и данную лицензию;</li>
    <li>отредактировали — опубликуйте исходный код под той же лицензией, и к ссылкам добавьте вашу версию.</li>
</ul>
</p>