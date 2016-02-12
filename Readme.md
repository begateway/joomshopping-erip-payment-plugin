## Платежный модуль JoomShopping для системы "Расчет" (ЕРИП)

### Установка

* Сделайте резевную копию вашего магазина и базы данных
* Скачайте модуль [plg_system_joomshoppingerip.zip](https://github.com/beGateway/joomshopping-erip-payment-plugin/raw/master/plg_system_joomshoppingerip.zip)
* Зайдите в панель администратора Joomla (www.yourshop.com/administrator)
* Выберите _Расширения_->_Менеджер Расширений_
* Загрузите и установите платежный модуль через **Загрузить файл пакета**.
* Выберите _Расширения_->_Менеджер плагинов_, найдите **System - JoomShopping ERIP Payment** и кликните на нем.
*	Убедитесь, что его _Состояние_ установленов в _Включено_ и нажмите _Сохранить и закрыть_.
*	Откройте _Компоненты_->_JoomShopping_->_Опции_ и выберите _Способы оплаты_.
* Нажмите _Создать_.
*	Настройте модуль
  * в _Название_ введите _Система "Расчет" (ЕРИП)_
  * в _Псевдоним_ введите _pm_erip_
  * Отметьте _Публикация_
  * нажмите _Сохранить и закрыть_
*	Откройте способ оплаты _pm_erip_ и нажмите закладку _Конфигурация_. Здесь необходимо заполнить
  * ID магазина, например, _363_
  * Ключ магазинa, например, _4f585d2709776e53d080f36872fd1b63b700733e7624dfcadd057296daa37df6_
  * Домен API, например, _api.bepaid.by_
  * Номер сервиса в системе ЕРИП
  * Имя компании в системе ЕРИП
  * Путь к услуге в дереве ЕРИП. Например, _Расчет (ЕРИП) -> Интернет-магазины/сервисы -> B -> bePaid.by_
  * Информация для плательщика для печати на чеке. Данный текст будет напечатан на чеке, подтверждающем оплату. Например, _Спасибо за оплату заказа %s_
  * Описание сервиса для плательщика. Например, _Оплата заказа %s_
  * Передавать данные о покупателе в ЕРИП. Если отмечено, то данные о покупателе будут переданы в ЕРИП и будут отображены покупателю в момент подтверждения оплаты
  * нажмите _Сохранить и закрыть_
* Модуль оплаты настроен.

### Работа с заказом

После получения заказа он по умолчанию в статусе _Pending_. Для того, чтобы получить оплату через ЕРИП,
Вам необходимо перевести его в статус _[ЕРИП] Ожидание оплаты_. Требование на оплату в системе ЕРИП будет сформировано и покупатель получит письмо с инструкцией как осуществить оплату через ЕРИП.

### Примечания

Протестировано и разработано для JoomShopping 4.11.0

Требуется PHP 5.3+

### Тестовые данные

Для тестирования оплаты через ЕРИП, введите в настройках номер сервиса _99999999_.
Через несколько секунд JoomShopping получит уведомление об оплате и переведет заказ в статус _Paid_.

## JoomShopping payment module for ERIP

### Installation

* Backup your webstore and database
* Download [plg_system_joomshoppingerip.zip](https://github.com/beGateway/joomshopping-erip-payment-plugin/raw/master/plg_system_joomshoppingerip.zip)
* Start up the administrative panel for Joomla (www.yourshop.com/administrator)
* Choose _Extensions_->_Extension Manager_
* Upload and install the payment module archive via **Upload Package File**.
* Choose _Extensions_->_Plugin Manager_ and find **System - JoomShopping ERIP Payment** plugin and click it.
*	Make sure that its status is set to _Enabled_ and press _Save & Close_.
*	Open _Components_->_JoomShopping_->_Options_ and select _Payments_.
* Press _New_.
*	Configure it
  * set _Title_ to _Raschet (ERIP)_
  * set _Alias_ to _pm_erip_
  * set _Published_ to _Yes_
  * click _Save & Close_
*	Open _pm_erip_ payment method and go to _Configuration_. Here you fill in
  * Shop Id, e.g. _363_
  * Shop secret key, e.g. _4f585d2709776e53d080f36872fd1b63b700733e7624dfcadd057296daa37df6_
  * API host name, e.g. _api.bepaid.by_
  * Service number in ERIP
  * Company name. Your company name in ERIP
  * Tree path. Your service tree path in ERIP
  * Service text. E.g. _Order %s payment_
  * Receipt text. The text will be printed at a payment confirmation receipt. E.g. _Thank you for the order %s payment_
  * Send customer details to ERIP. If checked, customer's details will be sent to ERIP and be shown at payment confirmation page
  * click _Save & Close_
* Now the module is configured.

### How to work with an order

When an order is received, it has _Pending_ status. If you want to get a payment via ERIP,
change the order status to _[ERIP] Awaiting payment_. A payment order will be sent to ERIP and your
customer will receive an email with instructions how to pay.

### Notes

Tested and developed with JoomShopping 4.11.0

PHP 5.3+ is required

### Test data

To test a payment via ERIP, enter in the module settings Service number _99999999_.
In few seconds JoomShopping will receive a notification that the order is paid.

### Contributing

Issue pull requests or send feature requests.
