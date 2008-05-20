<?php
/**
  * SpamPrevent
  *
  * Eresus 2
  *
  * Защита E-mail адресов от спам-роботов
  *
  * @version 1.04
  *
  * @copyright   2007-2008, Eresus Group, http://eresus.ru/
  * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
  * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
  * @author      Mikhail Krasilnikov <mk@procreat.ru>
  *
  * Данная программа является свободным программным обеспечением. Вы
  * вправе распространять ее и/или модифицировать в соответствии с
  * условиями версии 3 либо (по вашему выбору) с условиями более поздней
  * версии Стандартной Общественной Лицензии GNU, опубликованной Free
  * Software Foundation.
  *
  * Мы распространяем эту программу в надежде на то, что она будет вам
  * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
  * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
  * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
  * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
  *
  * Вы должны были получить копию Стандартной Общественной Лицензии
  * GNU с этой программой. Если Вы ее не получили, смотрите документ на
  * <http://www.gnu.org/licenses/>
  */

class SpamPrevent extends Plugin {
  var $version = '1.04';
  var $kernel = '2.10rc';
	var $type = 'client';
  var $title = 'SpamPrevent';
  var $description = 'Защита E-mail адресов от спам-роботов';
  var $settings = array(
    'href_method' => 'onmouseover',
    'href_fake_email' => 'null@example.com',
    'text_method' => 'entity',
  );
  /**
   * Конструктор
   *
   * @return TSpamPrevent
   */
  function SpamPrevent()
  {
    parent::Plugin();
    $this->listenEvents('clientOnPageRender');
  }
  //-----------------------------------------------------------------------------
  /**
   * Настройки плагина
   *
   * @return string  Диалог настроек
   */
  function settings()
  {
    global $page;

    $form = array(
      'name'=>'SettingsForm',
      'caption' => $this->title.' '.$this->version,
      'width' => '500px',
      'fields' => array (
        array('type'=>'hidden','name'=>'update', 'value'=>$this->name),
        array('type'=>'text', 'value'=>'SpamPrevent изменяет все адреса e-mail на страницах таким образом, чтобы скрыть их от роботов, собирающих базы адресов для спамеров.'),
        array('type'=>'header', 'value'=>'Защита адресов в ссылках'),
        array('type'=>'select', 'name' => 'href_method', 'label' => 'Метод', 'items' => array('(не использовать защиту)', 'JavaScript - подставлять адрес только при наведении мыши'), 'values' => array('none', 'onmouseover')),
        array('type'=>'edit', 'name' => 'href_fake_email', 'label' => 'Фиктивный адрес', 'width' => '100%'),
        array('type'=>'header', 'value'=>'Защита адресов в тексте'),
        array('type'=>'select', 'name' => 'text_method', 'label' => 'Метод', 'items' => array('(не использовать защиту)', 'Конвертировать символы адреса в спец.коды'), 'values' => array('none', 'entity')),
      ),
      'buttons' => array('ok', 'apply', 'cancel'),
    );
    $result = $page->renderForm($form, $this->settings);
    return $result;
  }
  //-----------------------------------------------------------------------------
  /**
   * Обработчик события clientOnPageRender
   *
   * @param string $text  Исходный текст страницы
   * @return string
   */
  function clientOnPageRender($text)
  {
    global $page;

    define('local_chars', '\d\w!#$%&\'*+\-\/=?^_`{|}~');
    define('local_part', '['.local_chars.']['.local_chars.'.]{0,63}');
    define('server_part', '[\d\w][\d\w\-]+\.[\d\w\-.]{2,}');
    if ($this->settings['href_method'] != 'none') {
      preg_match_all('/<a\s+.*href="mailto:([^"]+)"(.*)>/Ui', $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
      $delta = 0;
      for($i = 0; $i < count($matches); $i++) {
        switch ($this->settings['href_method']) {
          case 'onmouseover':
            $text = substr_replace($text, $this->settings['href_fake_email'], $matches[$i][1][1]+$delta, strlen($matches[$i][1][0]));
            $delta += strlen($this->settings['href_fake_email']) - strlen($matches[$i][1][0]);
            $mail = chunk_split('mailto:'.$matches[$i][1][0], mt_rand(3, 6), "'+'");
            $code = ' onmouseover="this.href=\''.$mail.'\'"';
            $text = substr_replace($text, $code, $matches[$i][2][1]+$delta, 0);
            $delta += strlen($code);
          break;
        }
      }
    }
    if ($this->settings['text_method'] != 'none') {
      preg_match_all('/(mailto:|[^'.local_chars.'])('.local_part.'@'.server_part.')/i', $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
      $delta = 0;
      for($i = 0; $i < count($matches); $i++) if (!preg_match('/mailto:/i', $matches[$i][0][0])) {
        switch ($this->settings['text_method']) {
          case 'entity':
            $replace = '';
            for($j = 0; $j < strlen($matches[$i][2][0]); $j++) $replace .= '&#'.ord($matches[$i][2][0]{$j}).';';
            $text = substr_replace($text, $replace, $matches[$i][2][1]+$delta, strlen($matches[$i][2][0]));
            $delta += strlen($replace) - strlen($matches[$i][2][0]);
          break;
        }
      }
    }
    return $text;
  }
  //-----------------------------------------------------------------------------

}
?>