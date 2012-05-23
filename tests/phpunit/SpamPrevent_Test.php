<?php
/**
 * SpamPrevent
 *
 * Модульные тесты
 *
 * @version ${product.version}
 *
 * @copyright 2012, ООО "Два слона", http://dvaslona.ru/
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
 * @subpackage Tests
 *
 * $Id: bootstrap.php 2173 2012-05-18 14:45:27Z mk $
 */


require_once __DIR__ . '/bootstrap.php';
require_once TESTS_SRC_DIR . '/spamprevent.php';

/**
 * @package SpamPrevent
 * @subpackage Tests
 */
class SpamPrevent_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers SpamPrevent::encodeHref
	 */
	public function test_encodeHref()
	{
		$plugin = $this->getMockBuilder('SpamPrevent')->disableOriginalConstructor()->getMock();

		$m_encodeHref = new ReflectionMethod('SpamPrevent', 'encodeHref');
		$m_encodeHref->setAccessible(true);

		$encoded = $m_encodeHref->invoke($plugin, 'user@example.org');
		$this->assertContains('null@example.com', $encoded);
		$this->assertContains('onmouseover', $encoded);
		$this->assertNotContains('user@example.org', $encoded);
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers SpamPrevent::encodeHref
	 * @expectedException LogicException
	 */
	public function test_encodeHref_invalidMethod()
	{
		$plugin = $this->getMockBuilder('SpamPrevent')->disableOriginalConstructor()->getMock();
		$plugin->settings['href_method'] = 'foo';

		$m_encodeHref = new ReflectionMethod('SpamPrevent', 'encodeHref');
		$m_encodeHref->setAccessible(true);

		$m_encodeHref->invoke($plugin, 'bar');
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers SpamPrevent::encodeText
	 */
	public function test_encodeText()
	{
		$plugin = $this->getMockBuilder('SpamPrevent')->disableOriginalConstructor()->getMock();

		$m_encodeText = new ReflectionMethod('SpamPrevent', 'encodeText');
		$m_encodeText->setAccessible(true);

		$encoded = $m_encodeText->invoke($plugin, 'тест123');
		$this->assertEquals('&#1090;&#1077;&#1089;&#1090;&#49;&#50;&#51;', $encoded);

		$encoded = $m_encodeText->invoke($plugin, 'user@example.org');
		$this->assertEquals('&#117;&#115;&#101;&#114;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;' .
			'&#46;&#111;&#114;&#103;', $encoded);

	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers SpamPrevent::encodeText
	 * @expectedException LogicException
	 */
	public function test_encodeText_invalidMethod()
	{
		$plugin = $this->getMockBuilder('SpamPrevent')->disableOriginalConstructor()->getMock();
		$plugin->settings['text_method'] = 'foo';

		$m_encodeText = new ReflectionMethod('SpamPrevent', 'encodeText');
		$m_encodeText->setAccessible(true);

		$m_encodeText->invoke($plugin, 'bar');
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers SpamPrevent::clientBeforeSend
	 */
	public function test_clientBeforeSend()
	{
		$plugin = $this->getMockBuilder('SpamPrevent')->disableOriginalConstructor()->
			setMethods(array('none'))->getMock();

		$this->assertNotContains('my.mail123@sub.some-domain.org',
			$plugin->clientBeforeSend('<a href="mailto:my.mail123@sub.some-domain.org">…</a>'));

		$this->assertEquals('&#109;&#121;&#46;&#109;&#97;&#105;&#108;&#49;&#50;&#51;&#64;&#115;&#117;' .
			'&#98;&#46;&#115;&#111;&#109;&#101;&#45;&#100;&#111;&#109;&#97;&#105;&#110;&#46;&#111;' .
			'&#114;&#103;', $plugin->clientBeforeSend('my.mail123@sub.some-domain.org'));

		$this->assertEquals('mailto:my.mail123@sub.some-domain.org',
			$plugin->clientBeforeSend('mailto:my.mail123@sub.some-domain.org'));
	}
	//-----------------------------------------------------------------------------
}