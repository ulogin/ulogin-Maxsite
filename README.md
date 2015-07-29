# uLogin

Donate link: http://ulogin.ru  
Tags: ulogin, login, social, authorization  
Requires at least: 0.81
Tested up to: 0.9
Stable tag: 2.0  
License: GNU General Public License, version 2  

**uLogin** — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
а владельцам сайтов — получить дополнительный приток пользователей из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)

## Установка
- Загрузите папку ulogin из архива плагина в application/maxsite/plugins/
- Зайдите в  Админ-панель сайта MaxSute CMS.
- В разделе "Плагины" перейдите во вкладку "Неактивные плагины" найдите ulogin 2.0 и нажмите кнопку "Включить".
- Модуль заработает сразу с настройками по умолчанию.

Более детальную информацию смотрите на сайте https://ulogin.ru/help.php

*Для корректной работы плагина uLogin рекомендуем использовать версию PHP 5.3 и выше.*
*При использовании плагина uLogin на версиях PHP 5.2 и ниже, необходимо проверить наличие подключённой библиотеки php_openssl.dll, если она отсутствует, то ещё необходимо подключить.*

## Модуль "uLogin"

Данный модуль находится в Админ-панели раздел *"Плагины"*.

Здесь задаются: 
 
**uLogin ID форма входа:** общее поле для всех виджетов uLogin, необязательный параметр (см. *"Настройки виджета uLogin"*);
**uLogin ID форма синхронизации:** общее поле для всех виджетов uLogin, необязательный параметр (см. *"Настройки виджета uLogin"*);
**Сохранять ссылку на профиль:** записывать в *Личные данные* пользователя поле *Вебсайт*;

## Настройки виджета uLogin

При установке расширения uLogin авторизация пользователей будет осуществляться с настройками по умолчанию.  
Для более детальной настройки виджетов uLogin Вы можете воспользоваться сервисом uLogin.  

Вы можете создать свой виджет uLogin и редактировать его самостоятельно:

- для создания виджета необходимо зайти в "Личный Кабинет" (ЛК) на сайте http://ulogin.ru/lk.php
- добавить свой сайт к списку "Мои сайты" и на вкладке "Виджеты" добавить новый виджет. После этого вы можете отредактировать свой виджет.

В графе "Возвращаемые поля профиля пользователя" вы можете включить необходимые поля, например, **Дата рождения**, MaxSite запишет этот параметр
в соответствующее поле Пользователя при регистрации, или обновит его при авторизации, если оно пустое.

**Важно!** Для успешной работы плагина необходимо включить в обязательных полях профиля поле **Еmail** в Личном кабинете uLogin.  
Заполнять поля в графе "Тип авторизации" не нужно, т.к. uLogin настроен на автоматическое заполнение данного параметра.

Созданный в Личном Кабинете виджет имеет параметры **uLogin ID**.  
Скопируйте значение **uLogin ID** вашего виджета в соответствующее поле в настройках плагина на вашем сайте и сохраните настройки.   

Если всё было сделано правильно, виджет изменится согласно вашим настройкам.


## Особенности

Для ручного вывода панели авторизации в любом месте шаблона MaxSite используйте класс ulogin функцию getPanelCode()

		require_once "ulogin.class.php";
		echo ulogin::getPanelCode();
	
Описание функции:

		/**
		* @param int $place - указывает, какую форму виджета необходимо выводить (0 - форма входа, 1 - форма синхронизации). Значение по умолчанию = 0
		* @return string(html)
		*/
		public static function getPanelCode($place = 0)`

Для вывода списка социальных аккаунтов пользователя MaxSite используйте класс ulogin функцию getuloginUserAccountsPanel()

		require_once "ulogin.class.php";
		ulogin::getuloginUserAccountsPanel();
	
Описание функции:

		/**
		*
		* @param int $user_id - ID пользователя (значение по умолчанию = текущий пользователь)
		* @return string(html)
		*/
		public static function getuloginUserAccountsPanel($user_id = 0)

## Изменения

####2.0.0.
  * Рефакторинг структуры и логики модуля.
  * Настройки модуля доступны в Админ-панели во вкладке *"Плагины" - "uLogin"*
  * Добавлен функционал синхронизации/привязки профилей uLogin в настройках профиля Пользователя.
  * Изменение в структуре таблицы *ulogintable*. Добавлены поля *network* и *identity*.
  * Реализована ajax синхронизация.
  * Улучшена генерация логина пользователя.
  * Добавлен функционал обновления аватара Пользователя из социальной сети при регистрации/авторизации размером 80х80px.
 
####1.0.0.
* Релиз.