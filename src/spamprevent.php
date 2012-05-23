<?php
/**
 * SpamPrevent
 *
 * Защита E-mail адресов от спам-роботов
 *
 * @version ${product.version}
 *
 * @copyright 2007, Михаил Красильников <mihalych@vsepofigu.ru>
 * @copyright 2009, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Михаил Красильников <mk@dvaslona.ru>
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
 *
 * @package SpamPrevent
 *
 * $Id$
 */

/**
 * Основной класс плагина
 *
 * @package SpamPrevent
 */
class SpamPrevent extends Plugin
{
	/**
	 * Версия плагина
	 *
	 * @var string
	 */
	public $version = '${product.version}';

	/**
	 * Минимальная версия ядра
	 *
	 * @var string
	 */
	public $kernel = '3.00b';

	/**
	 * Тип
	 *
	 * @var string
	 */
	public $type = 'client';

	/**
	 * Название
	 *
	 * @var string
	 */
	public $title = 'SpamPrevent';

	/**
	 * Описание
	 *
	 * @var string
	 */
	public $description = 'Защита E-mail адресов от спам-роботов';

	/**
	 * Настройки
	 *
	 * @var array
	 */
	public $settings = array(
		'href_method' => 'onmouseover',
		'href_fake_email' => 'null@example.com',
		'text_method' => 'entity',
	);

	/**
	 * Конструктор
	 *
	 * @return SpamPrevent
	 */
	public function __construct()
	{
		parent::__construct();
		$this->listenEvents('clientBeforeSend');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Настройки плагина
	 *
	 * @return string	Диалог настроек
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
				array('type'=>'text', 'value'=>'SpamPrevent изменяет все адреса e-mail на страницах ' .
					'таким образом, чтобы скрыть их от роботов, собирающих базы адресов для спамеров.'),
				array('type'=>'header', 'value'=>'Защита адресов в ссылках'),
				array('type'=>'select', 'name' => 'href_method', 'label' => 'Метод',
					'items' => array('(не использовать защиту)',
						'JavaScript - подставлять адрес только при наведении мыши'),
					'values' => array('none', 'onmouseover')),
				array('type'=>'edit', 'name' => 'href_fake_email', 'label' => 'Фиктивный адрес',
					'width' => '100%'),
				array('type'=>'header', 'value'=>'Защита адресов в тексте'),
				array('type'=>'select', 'name' => 'text_method', 'label' => 'Метод',
					'items' => array('(не использовать защиту)', 'Конвертировать символы адреса в спец.коды'),
					'values' => array('none', 'entity')),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $this->settings);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обработчик события clientBeforeSend
	 *
	 * @param string $text	Исходный текст страницы
	 * @return string
	 */
	function clientBeforeSend($text)
	{
		$local_part = '[^\x00-\x20]+';
		$server_part = '[\d\wа-яА-Я][\d\wа-яА-Я\-]+\.([\d\w\-.]{2,}|рф)';
		if ($this->settings['href_method'] != 'none')
		{
			preg_match_all('/<a\s+.*href="mailto:([^"]+)"(.*)>/Ui', $text, $matches,
				PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			$delta = 0;
			for ($i = 0; $i < count($matches); $i++)
			{
				$replace = $this->encodeHref($matches[$i][1][0]);
				$text = substr_replace($text, $replace, $matches[$i][1][1] + $delta,
					strlen($matches[$i][1][0]));
			}
		}
		if ($this->settings['text_method'] != 'none')
		{
			preg_match_all('/(' . $local_part . '@' . $server_part . ')/i', $text, $matches,
				PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			$delta = 0;
			for ($i = 0; $i < count($matches); $i++)
			{
				if (!preg_match('/mailto:/i', $matches[$i][0][0]))
				{
					$replace = $this->encodeText($matches[$i][1][0]);
					$text = substr_replace($text, $replace, $matches[$i][1][1] + $delta,
						strlen($matches[$i][1][0]));
					$delta += strlen($replace) - strlen($matches[$i][1][0]);
				}
			}
		}
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Кодирует ссылку
	 *
	 * @param string $source
	 *
	 * @throws LogicException  если в настройках указан неподдерживаемый метод кодировки
	 *
	 * @return string
	 *
	 * @since 2.00
	 */
	private function encodeHref($source)
	{
		switch ($this->settings['href_method'])
		{
			case 'onmouseover':
				$replace = $this->settings['href_fake_email'];
				$mail = chunk_split('mailto:' . $source, mt_rand(3, 6), "'+'");
				$replace .= '" onmouseover="this.href=\''.$mail.'\'';
				break;

			default:
				throw new LogicException(
					"Unknown href encoding method: \"{$this->settings['text_method']}\"");
		}
		return $replace;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Кодирует строку текста
	 *
	 * @param string $source
	 *
	 * @throws LogicException  если в настройках указан неподдерживаемый метод кодировки
	 *
	 * @return string
	 *
	 * @since 2.00
	 */
	private function encodeText($source)
	{
		switch ($this->settings['text_method'])
		{
			case 'entity':
				$replace = mb_encode_numericentity($source, array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
				break;

			default:
				throw new LogicException(
					"Unknown text encoding method: \"{$this->settings['text_method']}\"");
		}
		return $replace;
	}
	//-----------------------------------------------------------------------------
}
