<?php

require_once SYSPATH.'ee/EllisLab/ExpressionEngine/Boot/boot.common.php';
require_once APPPATH.'helpers/string_helper.php';
require_once APPPATH.'helpers/security_helper.php';
require_once APPPATH.'libraries/Functions.php';
require_once APPPATH.'libraries/Typography.php';
require_once APPPATH.'libraries/typography/Markdown/Michelf/MarkdownExtra.inc.php';

define('PATH_ADDONS', APPPATH.'modules/');
define('REQ', FALSE);

class NoneNoneTest extends \PHPUnit_Framework_TestCase {

	private $typography;

	public function setUp()
	{
		$this->typography = new EE_Typography();
	}

	private function getContentForMarkup($name)
	{
		$path = realpath(__DIR__.'/../../../../support/typography/' . $name);
		return file_get_contents($path);
	}

	/**
	 * There are number of preferences we can pass to the parse_type
	 * method that we need to test:
	 *
	 *  - text_format (xhtml, markdown, br, none, lite)
	 *  - html_format (safe, all, none)
	 *  - auto_links (y, n)
	 *  - allow_img_url (n, y)
	 */

    /**
     * @dataProvider dataProvider
     */
    public function testParseType($description, $in, $out)
    {
        $prefs = array(
            'text_format' => 'none',
            'html_format' => 'none',
            'auto_links' => 'n',
            'allow_img_url' => 'y',
        );

        $title = $this->typography->parse_type($in, $prefs);
        $this->assertEquals($title, $out, '[No Format/HTML] ' . $description);
    }

	public function dataProvider()
	{
		$data = array(
			array('Empty string', '', ''),
		);

		$data = array_merge(
			$data,
			$this->whitespaceData(),
			$this->punctuationData(),
			$this->HTMLWithClosingTagsData(),
			$this->HTMLWithoutClosingTagsData(),
			$this->HTMLWithContentData(),
			$this->HTMLAttributesData(),
			$this->HTMLWithAttributesData()
		);

		array_walk($data, function (&$datum) {
			$datum[2] = str_replace(array("<", ">"), array("&lt;", "&gt;"), $datum[2]);
		});

		// $markdown = $this->markdownData();
		//
		// array_walk($markdown, function (&$datum) {
		// 	$datum[2] = str_replace(array("<", ">"), array("&lt;", "&gt;"), $datum[1]);
		// });
		//
		// $data = array_merge(
		// 	$data,
		// 	$markdown
		// );

		array_walk($data, function (&$datum) {
			$datum[2] = ee()->functions->encode_ee_tags($datum[2], TRUE);
		});

		$data = array_merge(
			$data,
			$this->BBCodeData()
		);

		return $data;
	}

	protected function whitespaceData()
	{
		return array(
			array('Single spaces', 'a b', 'a b'),
			array('Double spaces', 'a  b', 'a  b'),
			array('Double spaces after a sentence', 'Word.  Word', 'Word.  Word'),
			array('Tabs are not converted', "a\t\tb", "a\t\tb"),
			array('A return is not converted', "a\nb", "a\nb"),
			array('Multiple returns are not converted', "a\n\nb", "a\n\nb"),
		);
	}

	protected function punctuationData()
	{
		return array(
			array('Backtick', '`', '`'),
			array('Tilde', '~', '~'),
			array('Exclamation point', '!', '!'),
			array('At sign', '@', '@'),
			array('Pound/hash sign', '#', '#'),
			array('Dollar sign', '$', '$'),
			array('Percent sign', '%', '%'),
			array('Circumflex', '^', '^'),
			array('Ampersand', '&', '&'),
			array('Asterisk', '*', '*'),
			array('Parentheses', '()', '()'),
			array('Underscore', '_', '_'),
			array('Hyphen', '-', '-'),
			array('Equals sign', '=', '='),
			array('Plus sign', '+', '+'),
			array('Brackets', '[]', '[]'),
			array('Braces', '{}', '&#123;&#125;'),
			array('Backslash', '\\', '\\'),
			array('Pipe', '|', '|'),
			array('Semicolon', ';', ';'),
			array('Colon', ':', ':'),
			array('Single quote', "'", "'"),
			array('Double quote', '"', '"'),
			array('Comma', ',', ','),
			array('Period', '.', '.'),
			array('Angle brackets', '<>', '<>'),
			array('Forward slash', '/', '/'),
			array('Question mark', '?', '?'),
			array('Em dash', '--', '--'),
			array('Ellipses', 'you know...', 'you know...'),
			array('Apostrophy', "It's its", "It's its"),
			array('US quote no whitespace', '"Hello"', '"Hello"'),
			array('UK quote no whitespace', "'Hello'", "'Hello'"),
			array('Simple nested US quote', '"I said, \'Hello\'"', '"I said, \'Hello\'"'),
			array('Simple nested UK quote', "'I said, \"Hello\"'", "'I said, \"Hello\"'"),
			array('US quote surrounded by whitespace', 'Foo "Bar" Baz', 'Foo "Bar" Baz'),
			array('UK quote surrounded by whitespace', "Foo 'Bar' Baz", "Foo 'Bar' Baz"),
		);
	}

	protected function HTMLWithClosingTagsData()
	{
		return array(
			array('<!-- -->', '<!-- -->', '&lt;!-- --&gt;'),
			array('<a></a>', '<a></a>', '&lt;a&gt;&lt;/a&gt;'),
			array('<abbr></abbr>', '<abbr></abbr>', '&lt;abbr&gt;&lt;/abbr&gt;'),
			array('<acronym></acronym>', '<acronym></acronym>', '&lt;acronym&gt;&lt;/acronym&gt;'),
			array('<address></address>', '<address></address>', '&lt;address&gt;&lt;/address&gt;'),
			array('<applet></applet>', '<applet></applet>', '&lt;applet&gt;&lt;/applet&gt;'),
			array('<area></area>', '<area></area>', '&lt;area&gt;&lt;/area&gt;'),
			array('<article></article>', '<article></article>', '&lt;article&gt;&lt;/article&gt;'),
			array('<aside></aside>', '<aside></aside>', '&lt;aside&gt;&lt;/aside&gt;'),
			array('<audio></audio>', '<audio></audio>','&lt;audio&gt;&lt;/audio&gt;'),
			array('<b></b>', '<b></b>', '&lt;b&gt;&lt;/b&gt;'),
			array('<base></base>', '<base></base>', '&lt;base&gt;&lt;/base&gt;'),
			array('<basefont></basefont>', '<basefont></basefont>', '&lt;basefont&gt;&lt;/basefont&gt;'),
			array('<bdi></bdi>', '<bdi></bdi>', '&lt;bdi&gt;&lt;/bdi&gt;'),
			array('<bdo></bdo>', '<bdo></bdo>', '&lt;bdo&gt;&lt;/bdo&gt;'),
			array('<big></big>', '<big></big>', '&lt;big&gt;&lt;/big&gt;'),
			array('<blockquote></blockquote>', '<blockquote></blockquote>', '&lt;blockquote&gt;&lt;/blockquote&gt;'),
			array('<body></body>', '<body></body>', '&lt;body&gt;&lt;/body&gt;'),
			array('<button></button>', '<button></button>', '&lt;button&gt;&lt;/button&gt;'),
			array('<canvas></canvas>', '<canvas></canvas>', '&lt;canvas&gt;&lt;/canvas&gt;'),
			array('<caption></caption>', '<caption></caption>', '&lt;caption&gt;&lt;/caption&gt;'),
			array('<center></center>', '<center></center>', '&lt;center&gt;&lt;/center&gt;'),
			array('<cite></cite>', '<cite></cite>', '&lt;cite&gt;&lt;/cite&gt;'),
			array('<code></code>', '<code></code>', '<code></code>'),
			array('<col></col>', '<col></col>', '&lt;col&gt;&lt;/col&gt;'),
			array('<colgroup></colgroup>', '<colgroup></colgroup>', '&lt;colgroup&gt;&lt;/colgroup&gt;'),
			array('<datalist></datalist>', '<datalist></datalist>', '&lt;datalist&gt;&lt;/datalist&gt;'),
			array('<dd></dd>', '<dd></dd>', '&lt;dd&gt;&lt;/dd&gt;'),
			array('<del></del>', '<del></del>', '&lt;del&gt;&lt;/del&gt;'),
			array('<details></details>', '<details></details>', '&lt;details&gt;&lt;/details&gt;'),
			array('<dfn></dfn>', '<dfn></dfn>', '&lt;dfn&gt;&lt;/dfn&gt;'),
			array('<dialog></dialog>', '<dialog></dialog>', '&lt;dialog&gt;&lt;/dialog&gt;'),
			array('<dir></dir>', '<dir></dir>', '&lt;dir&gt;&lt;/dir&gt;'),
			array('<div></div>', '<div></div>', '&lt;div&gt;&lt;/div&gt;'),
			array('<dl></dl>', '<dl></dl>', '&lt;dl&gt;&lt;/dl&gt;'),
			array('<dt></dt>', '<dt></dt>', '&lt;dt&gt;&lt;/dt&gt;'),
			array('<em></em>', '<em></em>', '&lt;em&gt;&lt;/em&gt;'),
			array('<embed></embed>', '<embed></embed>', '&lt;embed&gt;&lt;/embed&gt;'),
			array('<fieldset></fieldset>', '<fieldset></fieldset>', '&lt;fieldset&gt;&lt;/fieldset&gt;'),
			array('<figcaption></figcaption>', '<figcaption></figcaption>', '&lt;figcaption&gt;&lt;/figcaption&gt;'),
			array('<figure></figure>', '<figure></figure>', '&lt;figure&gt;&lt;/figure&gt;'),
			array('<font></font>', '<font></font>', '&lt;font&gt;&lt;/font&gt;'),
			array('<footer></footer>', '<footer></footer>', '&lt;footer&gt;&lt;/footer&gt;'),
			array('<form></form>', '<form></form>', '&lt;form&gt;&lt;/form&gt;'),
			array('<frame></frame>', '<frame></frame>', '&lt;frame&gt;&lt;/frame&gt;'),
			array('<frameset></frameset>', '<frameset></frameset>', '&lt;frameset&gt;&lt;/frameset&gt;'),
			array('<h1></h1>', '<h1></h1>', '&lt;h1&gt;&lt;/h1&gt;'),
			array('<h2></h2>', '<h2></h2>', '&lt;h2&gt;&lt;/h2&gt;'),
			array('<h3></h3>', '<h3></h3>', '&lt;h3&gt;&lt;/h3&gt;'),
			array('<h4></h4>', '<h4></h4>', '&lt;h4&gt;&lt;/h4&gt;'),
			array('<h5></h5>', '<h5></h5>', '&lt;h5&gt;&lt;/h5&gt;'),
			array('<h6></h6>', '<h6></h6>', '&lt;h6&gt;&lt;/h6&gt;'),
			array('<head></head>', '<head></head>', '&lt;head&gt;&lt;/head&gt;'),
			array('<header></header>', '<header></header>', '&lt;header&gt;&lt;/header&gt;'),
			array('<html></html>', '<html></html>', '&lt;html&gt;&lt;/html&gt;'),
			array('<i></i>', '<i></i>', '&lt;i&gt;&lt;/i&gt;'),
			array('<iframe></iframe>', '<iframe></iframe>', '&lt;iframe&gt;&lt;/iframe&gt;'),
			array('<ins></ins>', '<ins></ins>', '&lt;ins&gt;&lt;/ins&gt;'),
			array('<kbd></kbd>', '<kbd></kbd>', '&lt;kbd&gt;&lt;/kbd&gt;'),
			array('<keygen></keygen>', '<keygen></keygen>', '&lt;keygen&gt;&lt;/keygen&gt;'),
			array('<label></label>', '<label></label>', '&lt;label&gt;&lt;/label&gt;'),
			array('<legend></legend>', '<legend></legend>', '&lt;legend&gt;&lt;/legend&gt;'),
			array('<li></li>', '<li></li>', '&lt;li&gt;&lt;/li&gt;'),
			array('<link></link>', '<link></link>', '&lt;link&gt;&lt;/link&gt;'),
			array('<main></main>', '<main></main>', '&lt;main&gt;&lt;/main&gt;'),
			array('<map></map>', '<map></map>', '&lt;map&gt;&lt;/map&gt;'),
			array('<mark></mark>', '<mark></mark>', '<mark></mark>'),
			array('<menu></menu>', '<menu></menu>', '&lt;menu&gt;&lt;/menu&gt;'),
			array('<menuitem></menuitem>', '<menuitem></menuitem>', '&lt;menuitem&gt;&lt;/menuitem&gt;'),
			array('<meta></meta>', '<meta></meta>', '&lt;meta&gt;&lt;/meta&gt;'),
			array('<meter></meter>', '<meter></meter>', '&lt;meter&gt;&lt;/meter&gt;'),
			array('<nav></nav>', '<nav></nav>', '&lt;nav&gt;&lt;/nav&gt;'),
			array('<noframes></noframes>', '<noframes></noframes>', '&lt;noframes&gt;&lt;/noframes&gt;'),
			array('<noscript></noscript>', '<noscript></noscript>', '&lt;noscript&gt;&lt;/noscript&gt;'),
			array('<object></object>', '<object></object>', '&lt;object&gt;&lt;/object&gt;'),
			array('<ol></ol>', '<ol></ol>', '&lt;ol&gt;&lt;/ol&gt;'),
			array('<optgroup></optgroup>', '<optgroup></optgroup>', '&lt;optgroup&gt;&lt;/optgroup&gt;'),
			array('<option></option>', '<option></option>', '&lt;option&gt;&lt;/option&gt;'),
			array('<output></output>', '<output></output>', '&lt;output&gt;&lt;/output&gt;'),
			array('<p></p>', '<p></p>', '&lt;p&gt;&lt;/p&gt;'),
			array('<param></param>', '<param></param>', '&lt;param&gt;&lt;/param&gt;'),
			array('<picture></picture>', '<picture></picture>','&lt;picture&gt;&lt;/picture&gt;'),
			array('<pre></pre>', '<pre></pre>', '&lt;pre&gt;&lt;/pre&gt;'),
			array('<progress></progress>', '<progress></progress>', '&lt;progress&gt;&lt;/progress&gt;'),
			array('<q></q>', '<q></q>', '&lt;q&gt;&lt;/q&gt;'),
			array('<rp></rp>', '<rp></rp>', '&lt;rp&gt;&lt;/rp&gt;'),
			array('<rt></rt>', '<rt></rt>', '&lt;rt&gt;&lt;/rt&gt;'),
			array('<ruby></ruby>', '<ruby></ruby>', '&lt;ruby&gt;&lt;/ruby&gt;'),
			array('<s></s>', '<s></s>', '&lt;s&gt;&lt;/s&gt;'),
			array('<samp></samp>', '<samp></samp>', '&lt;samp&gt;&lt;/samp&gt;'),
			array('<script></script>', '<script></script>', '&lt;script&gt;&lt;/script&gt;'),
			array('<section></section>', '<section></section>', '&lt;section&gt;&lt;/section&gt;'),
			array('<select></select>', '<select></select>', '&lt;select&gt;&lt;/select&gt;'),
			array('<small></small>', '<small></small>', '&lt;small&gt;&lt;/small&gt;'),
			array('<source></source>', '<source></source>', '&lt;source&gt;&lt;/source&gt;'),
			array('<span></span>', '<span></span>', '<span></span>'),
			array('<strike></strike>', '<strike></strike>', '&lt;strike&gt;&lt;/strike&gt;'),
			array('<strong></strong>', '<strong></strong>', '&lt;strong&gt;&lt;/strong&gt;'),
			array('<style></style>', '<style></style>', '&lt;style&gt;&lt;/style&gt;'),
			array('<sub></sub>', '<sub></sub>', '&lt;sub&gt;&lt;/sub&gt;'),
			array('<summary></summary>', '<summary></summary>', '&lt;summary&gt;&lt;/summary&gt;'),
			array('<sup></sup>', '<sup></sup>', '&lt;sup&gt;&lt;/sup&gt;'),
			array('<table></table>', '<table></table>', '&lt;table&gt;&lt;/table&gt;'),
			array('<tbody></tbody>', '<tbody></tbody>', '&lt;tbody&gt;&lt;/tbody&gt;'),
			array('<td></td>', '<td></td>', '&lt;td&gt;&lt;/td&gt;'),
			array('<textarea></textarea>', '<textarea></textarea>', '&lt;textarea&gt;&lt;/textarea&gt;'),
			array('<tfoot></tfoot>', '<tfoot></tfoot>', '&lt;tfoot&gt;&lt;/tfoot&gt;'),
			array('<th></th>', '<th></th>', '&lt;th&gt;&lt;/th&gt;'),
			array('<thead></thead>', '<thead></thead>', '&lt;thead&gt;&lt;/thead&gt;'),
			array('<time></time>', '<time></time>', '&lt;time&gt;&lt;/time&gt;'),
			array('<title></title>', '<title></title>', '&lt;title&gt;&lt;/title&gt;'),
			array('<tr></tr>', '<tr></tr>', '&lt;tr&gt;&lt;/tr&gt;'),
			array('<track></track>', '<track></track>', '&lt;track&gt;&lt;/track&gt;'),
			array('<tt></tt>', '<tt></tt>', '&lt;tt&gt;&lt;/tt&gt;'),
			array('<u></u>', '<u></u>', '&lt;u&gt;&lt;/u&gt;'),
			array('<ul></ul>', '<ul></ul>', '&lt;ul&gt;&lt;/ul&gt;'),
			array('<var></var>', '<var></var>', '&lt;var&gt;&lt;/var&gt;'),
			array('<video></video>', '<video></video>', '&lt;video&gt;&lt;/video&gt;'),
			array('<wbr></wbr>', '<wbr></wbr>', '&lt;wbr&gt;&lt;/wbr&gt;'),
		);
	}

	protected function HTMLWithoutClosingTagsData()
	{
		return array(
			array('<!--', '<!--', '&lt;!--'),
			array('<a>', '<a>', '&lt;a&gt;'),
			array('<abbr>', '<abbr>', '&lt;abbr&gt;'),
			array('<acronym>', '<acronym>', '&lt;acronym&gt;'),
			array('<address>', '<address>', '&lt;address&gt;'),
			array('<applet>', '<applet>', '&lt;applet&gt;'),
			array('<area>', '<area>', '&lt;area&gt;'),
			array('<article>', '<article>', '&lt;article&gt;'),
			array('<aside>', '<aside>', '&lt;aside&gt;'),
			array('<audio>', '<audio>', '&lt;audio&gt;'),
			array('<b>', '<b>', '&lt;b&gt;'),
			array('<base>', '<base>', '&lt;base&gt;'),
			array('<basefont>', '<basefont>', '&lt;basefont&gt;'),
			array('<bdi>', '<bdi>', '&lt;bdi&gt;'),
			array('<bdo>', '<bdo>', '&lt;bdo&gt;'),
			array('<big>', '<big>', '&lt;big&gt;'),
			array('<blockquote>', '<blockquote>', '&lt;blockquote&gt;'),
			array('<body>', '<body>', '&lt;body&gt;'),
			array('<br>', '<br>', '&lt;br&gt;'),
			array('<button>', '<button>', '&lt;button&gt;'),
			array('<canvas>', '<canvas>', '&lt;canvas&gt;'),
			array('<caption>', '<caption>', '&lt;caption&gt;'),
			array('<center>', '<center>', '&lt;center&gt;'),
			array('<cite>', '<cite>', '&lt;cite&gt;'),
			array('<code>', '<code>', '&lt;code&gt;'),
			array('<col>', '<col>', '&lt;col&gt;'),
			array('<colgroup>', '<colgroup>', '&lt;colgroup&gt;'),
			array('<datalist>', '<datalist>', '&lt;datalist&gt;'),
			array('<dd>', '<dd>', '&lt;dd&gt;'),
			array('<del>', '<del>', '&lt;del&gt;'),
			array('<details>', '<details>', '&lt;details&gt;'),
			array('<dfn>', '<dfn>', '&lt;dfn&gt;'),
			array('<dialog>', '<dialog>', '&lt;dialog&gt;'),
			array('<dir>', '<dir>', '&lt;dir&gt;'),
			array('<div>', '<div>', '&lt;div&gt;'),
			array('<dl>', '<dl>', '&lt;dl&gt;'),
			array('<dt>', '<dt>', '&lt;dt&gt;'),
			array('<em>', '<em>', '&lt;em&gt;'),
			array('<embed>', '<embed>', '&lt;embed&gt;'),
			array('<fieldset>', '<fieldset>', '&lt;fieldset&gt;'),
			array('<figcaption>', '<figcaption>', '&lt;figcaption&gt;'),
			array('<figure>', '<figure>', '&lt;figure&gt;'),
			array('<font>', '<font>', '&lt;font&gt;'),
			array('<footer>', '<footer>', '&lt;footer&gt;'),
			array('<form>', '<form>', '&lt;form&gt;'),
			array('<frame>', '<frame>', '&lt;frame&gt;'),
			array('<frameset>', '<frameset>', '&lt;frameset&gt;'),
			array('<h1>', '<h1>', '&lt;h1&gt;'),
			array('<h2>', '<h2>', '&lt;h2&gt;'),
			array('<h3>', '<h3>', '&lt;h3&gt;'),
			array('<h4>', '<h4>', '&lt;h4&gt;'),
			array('<h5>', '<h5>', '&lt;h5&gt;'),
			array('<h6>', '<h6>', '&lt;h6&gt;'),
			array('<head>', '<head>', '&lt;head&gt;'),
			array('<header>', '<header>', '&lt;header&gt;'),
			array('<hr>', '<hr>', '&lt;hr&gt;'),
			array('<html>', '<html>', '&lt;html&gt;'),
			array('<i>', '<i>', '&lt;i&gt;'),
			array('<iframe>', '<iframe>', '&lt;iframe&gt;'),
			array('<img>', '<img>', '&lt;img&gt;'),
			array('<input>', '<input>', '&lt;input&gt;'),
			array('<ins>', '<ins>', '&lt;ins&gt;'),
			array('<kbd>', '<kbd>', '&lt;kbd&gt;'),
			array('<keygen>', '<keygen>', '&lt;keygen&gt;'),
			array('<label>', '<label>', '&lt;label&gt;'),
			array('<legend>', '<legend>', '&lt;legend&gt;'),
			array('<li>', '<li>', '&lt;li&gt;'),
			array('<link>', '<link>', '&lt;link&gt;'),
			array('<main>', '<main>', '&lt;main&gt;'),
			array('<map>', '<map>', '&lt;map&gt;'),
			array('<mark>', '<mark>', '&lt;mark&gt;'),
			array('<menu>', '<menu>', '&lt;menu&gt;'),
			array('<menuitem>', '<menuitem>', '&lt;menuitem&gt;'),
			array('<meta>', '<meta>', '&lt;meta&gt;'),
			array('<meter>', '<meter>', '&lt;meter&gt;'),
			array('<nav>', '<nav>', '&lt;nav&gt;'),
			array('<noframes>', '<noframes>', '&lt;noframes&gt;'),
			array('<noscript>', '<noscript>', '&lt;noscript&gt;'),
			array('<object>', '<object>', '&lt;object&gt;'),
			array('<ol>', '<ol>', '&lt;ol&gt;'),
			array('<optgroup>', '<optgroup>', '&lt;optgroup&gt;'),
			array('<option>', '<option>', '&lt;option&gt;'),
			array('<output>', '<output>', '&lt;output&gt;'),
			array('<p>', '<p>', '&lt;p&gt;'),
			array('<param>', '<param>', '&lt;param&gt;'),
			array('<picture>', '<picture>', '&lt;picture&gt;'),
			array('<pre>', '<pre>', '&lt;pre&gt;'),
			array('<progress>', '<progress>', '&lt;progress&gt;'),
			array('<q>', '<q>', '&lt;q&gt;'),
			array('<rp>', '<rp>', '&lt;rp&gt;'),
			array('<rt>', '<rt>', '&lt;rt&gt;'),
			array('<ruby>', '<ruby>', '&lt;ruby&gt;'),
			array('<s>', '<s>', '&lt;s&gt;'),
			array('<samp>', '<samp>', '&lt;samp&gt;'),
			array('<script>', '<script>', '&lt;script&gt;'),
			array('<section>', '<section>', '&lt;section&gt;'),
			array('<select>', '<select>', '&lt;select&gt;'),
			array('<small>', '<small>', '&lt;small&gt;'),
			array('<source>', '<source>', '&lt;source&gt;'),
			array('<span>', '<span>', '&lt;span&gt;'),
			array('<strike>', '<strike>', '&lt;strike&gt;'),
			array('<strong>', '<strong>', '&lt;strong&gt;'),
			array('<style>', '<style>', '&lt;style&gt;'),
			array('<sub>', '<sub>', '&lt;sub&gt;'),
			array('<summary>', '<summary>', '&lt;summary&gt;'),
			array('<sup>', '<sup>', '&lt;sup&gt;'),
			array('<table>', '<table>', '&lt;table&gt;'),
			array('<tbody>', '<tbody>', '&lt;tbody&gt;'),
			array('<td>', '<td>', '&lt;td&gt;'),
			array('<textarea>', '<textarea>', '&lt;textarea&gt;'),
			array('<tfoot>', '<tfoot>', '&lt;tfoot&gt;'),
			array('<th>', '<th>', '&lt;th&gt;'),
			array('<thead>', '<thead>', '&lt;thead&gt;'),
			array('<time>', '<time>', '&lt;time&gt;'),
			array('<title>', '<title>', '&lt;title&gt;'),
			array('<tr>', '<tr>', '&lt;tr&gt;'),
			array('<track>', '<track>', '&lt;track&gt;'),
			array('<tt>', '<tt>', '&lt;tt&gt;'),
			array('<u>', '<u>', '&lt;u&gt;'),
			array('<ul>', '<ul>', '&lt;ul&gt;'),
			array('<var>', '<var>', '&lt;var&gt;'),
			array('<video>', '<video>', '&lt;video&gt;'),
			array('<wbr>', '<wbr>', '&lt;wbr&gt;'),
		);
	}

	protected function HTMLWithContentData()
	{
		return array(
			array('<!-- foobar -->', '<!-- foobar -->', '&lt;!-- foobar --&gt;'),
			array('<a>foobar</a>', '<a>foobar</a>', '&lt;a&gt;foobar&lt;/a&gt;'),
			array('<abbr>foobar</abbr>', '<abbr>foobar</abbr>', '&lt;abbr&gt;foobar&lt;/abbr&gt;'),
			array('<acronym>foobar</acronym>', '<acronym>foobar</acronym>', '&lt;acronym&gt;foobar&lt;/acronym&gt;'),
			array('<address>foobar</address>', '<address>foobar</address>', '&lt;address&gt;foobar&lt;/address&gt;'),
			array('<applet>foobar</applet>', '<applet>foobar</applet>', '&lt;applet&gt;foobar&lt;/applet&gt;'),
			array('<area>foobar</area>', '<area>foobar</area>', '&lt;area&gt;foobar&lt;/area&gt;'),
			array('<article>foobar</article>', '<article>foobar</article>', '&lt;article&gt;foobar&lt;/article&gt;'),
			array('<aside>foobar</aside>', '<aside>foobar</aside>', '&lt;aside&gt;foobar&lt;/aside&gt;'),
			array('<audio>foobar</audio>', '<audio>foobar</audio>','&lt;audio&gt;foobar&lt;/audio&gt;'),
			array('<b>foobar</b>', '<b>foobar</b>', '<b>foobar</b>'),
			array('<base>foobar</base>', '<base>foobar</base>', '&lt;base&gt;foobar&lt;/base&gt;'),
			array('<basefont>foobar</basefont>', '<basefont>foobar</basefont>', '&lt;basefont&gt;foobar&lt;/basefont&gt;'),
			array('<bdi>foobar</bdi>', '<bdi>foobar</bdi>', '&lt;bdi&gt;foobar&lt;/bdi&gt;'),
			array('<bdo>foobar</bdo>', '<bdo>foobar</bdo>', '&lt;bdo&gt;foobar&lt;/bdo&gt;'),
			array('<big>foobar</big>', '<big>foobar</big>', '&lt;big&gt;foobar&lt;/big&gt;'),
			array('<blockquote>foobar</blockquote>', '<blockquote>foobar</blockquote>', '<blockquote>foobar</blockquote>'),
			array('<body>foobar</body>', '<body>foobar</body>', '&lt;body&gt;foobar&lt;/body&gt;'),
			array('<button>foobar</button>', '<button>foobar</button>', '&lt;button&gt;foobar&lt;/button&gt;'),
			array('<canvas>foobar</canvas>', '<canvas>foobar</canvas>', '&lt;canvas&gt;foobar&lt;/canvas&gt;'),
			array('<caption>foobar</caption>', '<caption>foobar</caption>', '&lt;caption&gt;foobar&lt;/caption&gt;'),
			array('<center>foobar</center>', '<center>foobar</center>', '&lt;center&gt;foobar&lt;/center&gt;'),
			array('<cite>foobar</cite>', '<cite>foobar</cite>', '<cite>foobar</cite>'),
			array('<code>foobar</code>', '<code>foobar</code>', '<code>foobar</code>'),
			array('<col>foobar</col>', '<col>foobar</col>', '&lt;col&gt;foobar&lt;/col&gt;'),
			array('<colgroup>foobar</colgroup>', '<colgroup>foobar</colgroup>', '&lt;colgroup&gt;foobar&lt;/colgroup&gt;'),
			array('<datalist>foobar</datalist>', '<datalist>foobar</datalist>', '&lt;datalist&gt;foobar&lt;/datalist&gt;'),
			array('<dd>foobar</dd>', '<dd>foobar</dd>', '&lt;dd&gt;foobar&lt;/dd&gt;'),
			array('<del>foobar</del>', '<del>foobar</del>', '<del>foobar</del>'),
			array('<details>foobar</details>', '<details>foobar</details>', '&lt;details&gt;foobar&lt;/details&gt;'),
			array('<dfn>foobar</dfn>', '<dfn>foobar</dfn>', '&lt;dfn&gt;foobar&lt;/dfn&gt;'),
			array('<dialog>foobar</dialog>', '<dialog>foobar</dialog>', '&lt;dialog&gt;foobar&lt;/dialog&gt;'),
			array('<dir>foobar</dir>', '<dir>foobar</dir>', '&lt;dir&gt;foobar&lt;/dir&gt;'),
			array('<div>foobar</div>', '<div>foobar</div>', '&lt;div&gt;foobar&lt;/div&gt;'),
			array('<dl>foobar</dl>', '<dl>foobar</dl>', '&lt;dl&gt;foobar&lt;/dl&gt;'),
			array('<dt>foobar</dt>', '<dt>foobar</dt>', '&lt;dt&gt;foobar&lt;/dt&gt;'),
			array('<em>foobar</em>', '<em>foobar</em>', '<em>foobar</em>'),
			array('<embed>foobar</embed>', '<embed>foobar</embed>', '&lt;embed&gt;foobar&lt;/embed&gt;'),
			array('<fieldset>foobar</fieldset>', '<fieldset>foobar</fieldset>', '&lt;fieldset&gt;foobar&lt;/fieldset&gt;'),
			array('<figcaption>foobar</figcaption>', '<figcaption>foobar</figcaption>', '&lt;figcaption&gt;foobar&lt;/figcaption&gt;'),
			array('<figure>foobar</figure>', '<figure>foobar</figure>', '&lt;figure&gt;foobar&lt;/figure&gt;'),
			array('<font>foobar</font>', '<font>foobar</font>', '&lt;font&gt;foobar&lt;/font&gt;'),
			array('<footer>foobar</footer>', '<footer>foobar</footer>', '&lt;footer&gt;foobar&lt;/footer&gt;'),
			array('<form>foobar</form>', '<form>foobar</form>', '&lt;form&gt;foobar&lt;/form&gt;'),
			array('<frame>foobar</frame>', '<frame>foobar</frame>', '&lt;frame&gt;foobar&lt;/frame&gt;'),
			array('<frameset>foobar</frameset>', '<frameset>foobar</frameset>', '&lt;frameset&gt;foobar&lt;/frameset&gt;'),
			array('<h1>foobar</h1>', '<h1>foobar</h1>', '&lt;h1&gt;foobar&lt;/h1&gt;'),
			array('<h2>foobar</h2>', '<h2>foobar</h2>', '<h2>foobar</h2>'),
			array('<h3>foobar</h3>', '<h3>foobar</h3>', '<h3>foobar</h3>'),
			array('<h4>foobar</h4>', '<h4>foobar</h4>', '<h4>foobar</h4>'),
			array('<h5>foobar</h5>', '<h5>foobar</h5>', '<h5>foobar</h5>'),
			array('<h6>foobar</h6>', '<h6>foobar</h6>', '<h6>foobar</h6>'),
			array('<head>foobar</head>', '<head>foobar</head>', '&lt;head&gt;foobar&lt;/head&gt;'),
			array('<header>foobar</header>', '<header>foobar</header>', '&lt;header&gt;foobar&lt;/header&gt;'),
			array('<html>foobar</html>', '<html>foobar</html>', '&lt;html&gt;foobar&lt;/html&gt;'),
			array('<i>foobar</i>', '<i>foobar</i>', '<i>foobar</i>'),
			array('<iframe>foobar</iframe>', '<iframe>foobar</iframe>', '&lt;iframe&gt;foobar&lt;/iframe&gt;'),
			array('<ins>foobar</ins>', '<ins>foobar</ins>', '<ins>foobar</ins>'),
			array('<kbd>foobar</kbd>', '<kbd>foobar</kbd>', '&lt;kbd&gt;foobar&lt;/kbd&gt;'),
			array('<keygen>foobar</keygen>', '<keygen>foobar</keygen>', '&lt;keygen&gt;foobar&lt;/keygen&gt;'),
			array('<label>foobar</label>', '<label>foobar</label>', '&lt;label&gt;foobar&lt;/label&gt;'),
			array('<legend>foobar</legend>', '<legend>foobar</legend>', '&lt;legend&gt;foobar&lt;/legend&gt;'),
			array('<li>foobar</li>', '<li>foobar</li>', '&lt;li&gt;foobar&lt;/li&gt;'),
			array('<link>foobar</link>', '<link>foobar</link>', '&lt;link&gt;foobar&lt;/link&gt;'),
			array('<main>foobar</main>', '<main>foobar</main>', '&lt;main&gt;foobar&lt;/main&gt;'),
			array('<map>foobar</map>', '<map>foobar</map>', '&lt;map&gt;foobar&lt;/map&gt;'),
			array('<mark>foobar</mark>', '<mark>foobar</mark>', '<mark>foobar</mark>'),
			array('<menu>foobar</menu>', '<menu>foobar</menu>', '&lt;menu&gt;foobar&lt;/menu&gt;'),
			array('<menuitem>foobar</menuitem>', '<menuitem>foobar</menuitem>', '&lt;menuitem&gt;foobar&lt;/menuitem&gt;'),
			array('<meta>foobar</meta>', '<meta>foobar</meta>', '&lt;meta&gt;foobar&lt;/meta&gt;'),
			array('<meter>foobar</meter>', '<meter>foobar</meter>', '&lt;meter&gt;foobar&lt;/meter&gt;'),
			array('<nav>foobar</nav>', '<nav>foobar</nav>', '&lt;nav&gt;foobar&lt;/nav&gt;'),
			array('<noframes>foobar</noframes>', '<noframes>foobar</noframes>', '&lt;noframes&gt;foobar&lt;/noframes&gt;'),
			array('<noscript>foobar</noscript>', '<noscript>foobar</noscript>', '&lt;noscript&gt;foobar&lt;/noscript&gt;'),
			array('<object>foobar</object>', '<object>foobar</object>', '&lt;object&gt;foobar&lt;/object&gt;'),
			array('<ol>foobar</ol>', '<ol>foobar</ol>', '&lt;ol&gt;foobar&lt;/ol&gt;'),
			array('<optgroup>foobar</optgroup>', '<optgroup>foobar</optgroup>', '&lt;optgroup&gt;foobar&lt;/optgroup&gt;'),
			array('<option>foobar</option>', '<option>foobar</option>', '&lt;option&gt;foobar&lt;/option&gt;'),
			array('<output>foobar</output>', '<output>foobar</output>', '&lt;output&gt;foobar&lt;/output&gt;'),
			array('<p>foobar</p>', '<p>foobar</p>', '&lt;p&gt;foobar&lt;/p&gt;'),
			array('<param>foobar</param>', '<param>foobar</param>', '&lt;param&gt;foobar&lt;/param&gt;'),
			array('<picture>foobar</picture>', '<picture>foobar</picture>','&lt;picture&gt;foobar&lt;/picture&gt;'),
			array('<pre>foobar</pre>', '<pre>foobar</pre>', '<pre>foobar</pre>'),
			array('<progress>foobar</progress>', '<progress>foobar</progress>', '&lt;progress&gt;foobar&lt;/progress&gt;'),
			array('<q>foobar</q>', '<q>foobar</q>', '&lt;q&gt;foobar&lt;/q&gt;'),
			array('<rp>foobar</rp>', '<rp>foobar</rp>', '&lt;rp&gt;foobar&lt;/rp&gt;'),
			array('<rt>foobar</rt>', '<rt>foobar</rt>', '&lt;rt&gt;foobar&lt;/rt&gt;'),
			array('<ruby>foobar</ruby>', '<ruby>foobar</ruby>', '&lt;ruby&gt;foobar&lt;/ruby&gt;'),
			array('<s>foobar</s>', '<s>foobar</s>', '&lt;s&gt;foobar&lt;/s&gt;'),
			array('<samp>foobar</samp>', '<samp>foobar</samp>', '&lt;samp&gt;foobar&lt;/samp&gt;'),
			array('<script>foobar</script>', '<script>foobar</script>', '&lt;script&gt;foobar&lt;/script&gt;'),
			array('<section>foobar</section>', '<section>foobar</section>', '&lt;section&gt;foobar&lt;/section&gt;'),
			array('<select>foobar</select>', '<select>foobar</select>', '&lt;select&gt;foobar&lt;/select&gt;'),
			array('<small>foobar</small>', '<small>foobar</small>', '&lt;small&gt;foobar&lt;/small&gt;'),
			array('<source>foobar</source>', '<source>foobar</source>', '&lt;source&gt;foobar&lt;/source&gt;'),
			array('<span>foobar</span>', '<span>foobar</span>', '<span>foobar</span>'),
			array('<strike>foobar</strike>', '<strike>foobar</strike>', '&lt;strike&gt;foobar&lt;/strike&gt;'),
			array('<strong>foobar</strong>', '<strong>foobar</strong>', '<strong>foobar</strong>'),
			array('<style>foobar</style>', '<style>foobar</style>', '&lt;style&gt;foobar&lt;/style&gt;'),
			array('<sub>foobar</sub>', '<sub>foobar</sub>', '<sub>foobar</sub>'),
			array('<summary>foobar</summary>', '<summary>foobar</summary>', '&lt;summary&gt;foobar&lt;/summary&gt;'),
			array('<sup>foobar</sup>', '<sup>foobar</sup>', '<sup>foobar</sup>'),
			array('<table>foobar</table>', '<table>foobar</table>', '&lt;table&gt;foobar&lt;/table&gt;'),
			array('<tbody>foobar</tbody>', '<tbody>foobar</tbody>', '&lt;tbody&gt;foobar&lt;/tbody&gt;'),
			array('<td>foobar</td>', '<td>foobar</td>', '&lt;td&gt;foobar&lt;/td&gt;'),
			array('<textarea>foobar</textarea>', '<textarea>foobar</textarea>', '&lt;textarea&gt;foobar&lt;/textarea&gt;'),
			array('<tfoot>foobar</tfoot>', '<tfoot>foobar</tfoot>', '&lt;tfoot&gt;foobar&lt;/tfoot&gt;'),
			array('<th>foobar</th>', '<th>foobar</th>', '&lt;th&gt;foobar&lt;/th&gt;'),
			array('<thead>foobar</thead>', '<thead>foobar</thead>', '&lt;thead&gt;foobar&lt;/thead&gt;'),
			array('<time>foobar</time>', '<time>foobar</time>', '&lt;time&gt;foobar&lt;/time&gt;'),
			array('<title>foobar</title>', '<title>foobar</title>', '&lt;title&gt;foobar&lt;/title&gt;'),
			array('<tr>foobar</tr>', '<tr>foobar</tr>', '&lt;tr&gt;foobar&lt;/tr&gt;'),
			array('<track>foobar</track>', '<track>foobar</track>', '&lt;track&gt;foobar&lt;/track&gt;'),
			array('<tt>foobar</tt>', '<tt>foobar</tt>', '&lt;tt&gt;foobar&lt;/tt&gt;'),
			array('<u>foobar</u>', '<u>foobar</u>', '&lt;u&gt;foobar&lt;/u&gt;'),
			array('<ul>foobar</ul>', '<ul>foobar</ul>', '&lt;ul&gt;foobar&lt;/ul&gt;'),
			array('<var>foobar</var>', '<var>foobar</var>', '&lt;var&gt;foobar&lt;/var&gt;'),
			array('<video>foobar</video>', '<video>foobar</video>', '&lt;video&gt;foobar&lt;/video&gt;'),
			array('<wbr>foobar</wbr>', '<wbr>foobar</wbr>', '&lt;wbr&gt;foobar&lt;/wbr&gt;'),
		);
	}

	protected function HTMLAttributesData()
	{
		return array(
			array('async', '<div async>', '&lt;div async&gt;'),
			array('autofocus', '<div autofocus>', '&lt;div autofocus&gt;'),
			array('autoplay', '<div autoplay>', '&lt;div autoplay&gt;'),
			array('challenge', '<div challenge>', '&lt;div challenge&gt;'),
			array('checked', '<div checked>', '&lt;div checked&gt;'),
			array('controls', '<div controls>', '&lt;div controls&gt;'),
			array('default', '<div default>', '&lt;div default&gt;'),
			array('defer', '<div defer>', '&lt;div defer&gt;'),
			array('disabled', '<div disabled>', '&lt;div disabled&gt;'),
			array('hidden', '<div hidden>', '&lt;div hidden&gt;'),
			array('ismap', '<div ismap>', '&lt;div ismap&gt;'),
			array('loop', '<div loop>', '&lt;div loop&gt;'),
			array('multiple', '<div multiple>', '&lt;div multiple&gt;'),
			array('muted', '<div muted>', '&lt;div muted&gt;'),
			array('novalidate', '<div novalidate>', '&lt;div novalidate&gt;'),
			array('readonly', '<div readonly>', '&lt;div readonly&gt;'),
			array('required', '<div required>', '&lt;div required&gt;'),
			array('reversed', '<div reversed>', '&lt;div reversed&gt;'),
			array('sandbox', '<div sandbox>', '&lt;div sandbox&gt;'),
			array('scoped', '<div scoped>', '&lt;div scoped&gt;'),
			array('selected', '<div selected>', '&lt;div selected&gt;'),

			array('accept', '<div accept="foo">', '&lt;div accept="foo"&gt;'),
			array('accept-charset', '<div accept-charset="foo">', '&lt;div accept-charset="foo"&gt;'),
			array('accesskey', '<div accesskey="foo">', '&lt;div accesskey="foo"&gt;'),
			array('action', '<div action="foo">', '&lt;div action="foo"&gt;'),
			array('align', '<div align="foo">', '&lt;div align="foo"&gt;'),
			array('alt', '<div alt="foo">', '&lt;div alt="foo"&gt;'),
			array('autocomplete', '<div autocomplete="foo">', '&lt;div autocomplete="foo"&gt;'),
			array('bgcolor', '<div bgcolor="foo">', '&lt;div bgcolor="foo"&gt;'),
			array('border', '<div border="foo">', '&lt;div border="foo"&gt;'),
			array('charset', '<div charset="foo">', '&lt;div charset="foo"&gt;'),
			array('cite', '<div cite="foo">', '&lt;div cite="foo"&gt;'),
			array('class', '<div class="foo">', '&lt;div class="foo"&gt;'),
			array('color', '<div color="foo">', '&lt;div color="foo"&gt;'),
			array('cols', '<div cols="foo">', '&lt;div cols="foo"&gt;'),
			array('colspan', '<div colspan="foo">', '&lt;div colspan="foo"&gt;'),
			array('content', '<div content="foo">', '&lt;div content="foo"&gt;'),
			array('contenteditable', '<div contenteditable="foo">', '&lt;div contenteditable="foo"&gt;'),
			array('contextmenu', '<div contextmenu="foo">', '&lt;div contextmenu="foo"&gt;'),
			array('coords', '<div coords="foo">', '&lt;div coords="foo"&gt;'),
			array('data', '<div data="foo">', '&lt;div data="foo"&gt;'),
			array('data', '<div data="foo">', '&lt;div data="foo"&gt;'),
			array('datetime', '<div datetime="foo">', '&lt;div datetime="foo"&gt;'),
			array('dir', '<div dir="foo">', '&lt;div dir="foo"&gt;'),
			array('dirname', '<div dirname="foo">', '&lt;div dirname="foo"&gt;'),
			array('download', '<div download="foo">', '&lt;div download="foo"&gt;'),
			array('draggable', '<div draggable="foo">', '&lt;div draggable="foo"&gt;'),
			array('dropzone', '<div dropzone="foo">', '&lt;div dropzone="foo"&gt;'),
			array('enctype', '<div enctype="foo">', '&lt;div enctype="foo"&gt;'),
			array('for', '<div for="foo">', '&lt;div for="foo"&gt;'),
			array('form', '<div form="foo">', '&lt;div form="foo"&gt;'),
			array('formaction', '<div formaction="foo">', '&lt;div formaction="foo"&gt;'),
			array('headers', '<div headers="foo">', '&lt;div headers="foo"&gt;'),
			array('height', '<div height="foo">', '&lt;div height="foo"&gt;'),
			array('high', '<div high="foo">', '&lt;div high="foo"&gt;'),
			array('href', '<div href="foo">', '&lt;div href="foo"&gt;'),
			array('hreflang', '<div hreflang="foo">', '&lt;div hreflang="foo"&gt;'),
			array('http', '<div http="foo">', '&lt;div http="foo"&gt;'),
			array('id', '<div id="foo">', '&lt;div id="foo"&gt;'),
			array('keytype', '<div keytype="foo">', '&lt;div keytype="foo"&gt;'),
			array('kind', '<div kind="foo">', '&lt;div kind="foo"&gt;'),
			array('label', '<div label="foo">', '&lt;div label="foo"&gt;'),
			array('lang', '<div lang="foo">', '&lt;div lang="foo"&gt;'),
			array('list', '<div list="foo">', '&lt;div list="foo"&gt;'),
			array('low', '<div low="foo">', '&lt;div low="foo"&gt;'),
			array('max', '<div max="foo">', '&lt;div max="foo"&gt;'),
			array('maxlength', '<div maxlength="foo">', '&lt;div maxlength="foo"&gt;'),
			array('media', '<div media="foo">', '&lt;div media="foo"&gt;'),
			array('method', '<div method="foo">', '&lt;div method="foo"&gt;'),
			array('min', '<div min="foo">', '&lt;div min="foo"&gt;'),
			array('name', '<div name="foo">', '&lt;div name="foo"&gt;'),
			array('onabort', '<div onabort="foo">', '&lt;div onabort="foo"&gt;'),
			array('onafterprint', '<div onafterprint="foo">', '&lt;div onafterprint="foo"&gt;'),
			array('onbeforeprint', '<div onbeforeprint="foo">', '&lt;div onbeforeprint="foo"&gt;'),
			array('onbeforeunload', '<div onbeforeunload="foo">', '&lt;div onbeforeunload="foo"&gt;'),
			array('onblur', '<div onblur="foo">', '&lt;div onblur="foo"&gt;'),
			array('oncanplay', '<div oncanplay="foo">', '&lt;div oncanplay="foo"&gt;'),
			array('oncanplaythrough', '<div oncanplaythrough="foo">', '&lt;div oncanplaythrough="foo"&gt;'),
			array('onchange', '<div onchange="foo">', '&lt;div onchange="foo"&gt;'),
			array('onclick', '<div onclick="foo">', '&lt;div onclick="foo"&gt;'),
			array('oncontextmenu', '<div oncontextmenu="foo">', '&lt;div oncontextmenu="foo"&gt;'),
			array('oncopy', '<div oncopy="foo">', '&lt;div oncopy="foo"&gt;'),
			array('oncuechange', '<div oncuechange="foo">', '&lt;div oncuechange="foo"&gt;'),
			array('oncut', '<div oncut="foo">', '&lt;div oncut="foo"&gt;'),
			array('ondblclick', '<div ondblclick="foo">', '&lt;div ondblclick="foo"&gt;'),
			array('ondrag', '<div ondrag="foo">', '&lt;div ondrag="foo"&gt;'),
			array('ondragend', '<div ondragend="foo">', '&lt;div ondragend="foo"&gt;'),
			array('ondragenter', '<div ondragenter="foo">', '&lt;div ondragenter="foo"&gt;'),
			array('ondragleave', '<div ondragleave="foo">', '&lt;div ondragleave="foo"&gt;'),
			array('ondragover', '<div ondragover="foo">', '&lt;div ondragover="foo"&gt;'),
			array('ondragstart', '<div ondragstart="foo">', '&lt;div ondragstart="foo"&gt;'),
			array('ondrop', '<div ondrop="foo">', '&lt;div ondrop="foo"&gt;'),
			array('ondurationchange', '<div ondurationchange="foo">', '&lt;div ondurationchange="foo"&gt;'),
			array('onemptied', '<div onemptied="foo">', '&lt;div onemptied="foo"&gt;'),
			array('onended', '<div onended="foo">', '&lt;div onended="foo"&gt;'),
			array('onerror', '<div onerror="foo">', '&lt;div onerror="foo"&gt;'),
			array('onfocus', '<div onfocus="foo">', '&lt;div onfocus="foo"&gt;'),
			array('onhashchange', '<div onhashchange="foo">', '&lt;div onhashchange="foo"&gt;'),
			array('oninput', '<div oninput="foo">', '&lt;div oninput="foo"&gt;'),
			array('oninvalid', '<div oninvalid="foo">', '&lt;div oninvalid="foo"&gt;'),
			array('onkeydown', '<div onkeydown="foo">', '&lt;div onkeydown="foo"&gt;'),
			array('onkeypress', '<div onkeypress="foo">', '&lt;div onkeypress="foo"&gt;'),
			array('onkeyup', '<div onkeyup="foo">', '&lt;div onkeyup="foo"&gt;'),
			array('onload', '<div onload="foo">', '&lt;div onload="foo"&gt;'),
			array('onloadeddata', '<div onloadeddata="foo">', '&lt;div onloadeddata="foo"&gt;'),
			array('onloadedmetadata', '<div onloadedmetadata="foo">', '&lt;div onloadedmetadata="foo"&gt;'),
			array('onloadstart', '<div onloadstart="foo">', '&lt;div onloadstart="foo"&gt;'),
			array('onmousedown', '<div onmousedown="foo">', '&lt;div onmousedown="foo"&gt;'),
			array('onmousemove', '<div onmousemove="foo">', '&lt;div onmousemove="foo"&gt;'),
			array('onmouseout', '<div onmouseout="foo">', '&lt;div onmouseout="foo"&gt;'),
			array('onmouseover', '<div onmouseover="foo">', '&lt;div onmouseover="foo"&gt;'),
			array('onmouseup', '<div onmouseup="foo">', '&lt;div onmouseup="foo"&gt;'),
			array('onmousewheel', '<div onmousewheel="foo">', '&lt;div onmousewheel="foo"&gt;'),
			array('onoffline', '<div onoffline="foo">', '&lt;div onoffline="foo"&gt;'),
			array('ononline', '<div ononline="foo">', '&lt;div ononline="foo"&gt;'),
			array('onpagehide', '<div onpagehide="foo">', '&lt;div onpagehide="foo"&gt;'),
			array('onpageshow', '<div onpageshow="foo">', '&lt;div onpageshow="foo"&gt;'),
			array('onpaste', '<div onpaste="foo">', '&lt;div onpaste="foo"&gt;'),
			array('onpause', '<div onpause="foo">', '&lt;div onpause="foo"&gt;'),
			array('onplay', '<div onplay="foo">', '&lt;div onplay="foo"&gt;'),
			array('onplaying', '<div onplaying="foo">', '&lt;div onplaying="foo"&gt;'),
			array('onpopstate', '<div onpopstate="foo">', '&lt;div onpopstate="foo"&gt;'),
			array('onprogress', '<div onprogress="foo">', '&lt;div onprogress="foo"&gt;'),
			array('onratechange', '<div onratechange="foo">', '&lt;div onratechange="foo"&gt;'),
			array('onreset', '<div onreset="foo">', '&lt;div onreset="foo"&gt;'),
			array('onresize', '<div onresize="foo">', '&lt;div onresize="foo"&gt;'),
			array('onscroll', '<div onscroll="foo">', '&lt;div onscroll="foo"&gt;'),
			array('onsearch', '<div onsearch="foo">', '&lt;div onsearch="foo"&gt;'),
			array('onseeked', '<div onseeked="foo">', '&lt;div onseeked="foo"&gt;'),
			array('onseeking', '<div onseeking="foo">', '&lt;div onseeking="foo"&gt;'),
			array('onselect', '<div onselect="foo">', '&lt;div onselect="foo"&gt;'),
			array('onshow', '<div onshow="foo">', '&lt;div onshow="foo"&gt;'),
			array('onstalled', '<div onstalled="foo">', '&lt;div onstalled="foo"&gt;'),
			array('onstorage', '<div onstorage="foo">', '&lt;div onstorage="foo"&gt;'),
			array('onsubmit', '<div onsubmit="foo">', '&lt;div onsubmit="foo"&gt;'),
			array('onsuspend', '<div onsuspend="foo">', '&lt;div onsuspend="foo"&gt;'),
			array('ontimeupdate', '<div ontimeupdate="foo">', '&lt;div ontimeupdate="foo"&gt;'),
			array('ontoggle', '<div ontoggle="foo">', '&lt;div ontoggle="foo"&gt;'),
			array('onunload', '<div onunload="foo">', '&lt;div onunload="foo"&gt;'),
			array('onvolumechange', '<div onvolumechange="foo">', '&lt;div onvolumechange="foo"&gt;'),
			array('onwaiting', '<div onwaiting="foo">', '&lt;div onwaiting="foo"&gt;'),
			array('onwheel', '<div onwheel="foo">', '&lt;div onwheel="foo"&gt;'),
			array('open', '<div open="foo">', '&lt;div open="foo"&gt;'),
			array('optimum', '<div optimum="foo">', '&lt;div optimum="foo"&gt;'),
			array('pattern', '<div pattern="foo">', '&lt;div pattern="foo"&gt;'),
			array('placeholder', '<div placeholder="foo">', '&lt;div placeholder="foo"&gt;'),
			array('poster', '<div poster="foo">', '&lt;div poster="foo"&gt;'),
			array('preload', '<div preload="foo">', '&lt;div preload="foo"&gt;'),
			array('rel', '<div rel="foo">', '&lt;div rel="foo"&gt;'),
			array('rows', '<div rows="foo">', '&lt;div rows="foo"&gt;'),
			array('rowspan', '<div rowspan="foo">', '&lt;div rowspan="foo"&gt;'),
			array('scope', '<div scope="foo">', '&lt;div scope="foo"&gt;'),
			array('shape', '<div shape="foo">', '&lt;div shape="foo"&gt;'),
			array('size', '<div size="foo">', '&lt;div size="foo"&gt;'),
			array('sizes', '<div sizes="foo">', '&lt;div sizes="foo"&gt;'),
			array('span', '<div span="foo">', '&lt;div span="foo"&gt;'),
			array('spellcheck', '<div spellcheck="foo">', '&lt;div spellcheck="foo"&gt;'),
			array('src', '<div src="foo">', '&lt;div src="foo"&gt;'),
			array('srcdoc', '<div srcdoc="foo">', '&lt;div srcdoc="foo"&gt;'),
			array('srclang', '<div srclang="foo">', '&lt;div srclang="foo"&gt;'),
			array('srcset', '<div srcset="foo">', '&lt;div srcset="foo"&gt;'),
			array('start', '<div start="foo">', '&lt;div start="foo"&gt;'),
			array('step', '<div step="foo">', '&lt;div step="foo"&gt;'),
			array('style', '<div style="foo">', '&lt;div style="foo"&gt;'),
			array('tabindex', '<div tabindex="foo">', '&lt;div tabindex="foo"&gt;'),
			array('target', '<div target="foo">', '&lt;div target="foo"&gt;'),
			array('title', '<div title="foo">', '&lt;div title="foo"&gt;'),
			array('translate', '<div translate="foo">', '&lt;div translate="foo"&gt;'),
			array('type', '<div type="foo">', '&lt;div type="foo"&gt;'),
			array('usemap', '<div usemap="foo">', '&lt;div usemap="foo"&gt;'),
			array('value', '<div value="foo">', '&lt;div value="foo"&gt;'),
			array('width', '<div width="foo">', '&lt;div width="foo"&gt;'),
			array('wrap', '<div wrap="foo">', '&lt;div wrap="foo"&gt;'),
		);
	}

	protected function HTMLWithAttributesData()
	{
		$all_attributes = 'async autofocus autoplay challenge checked controls default defer disabled hidden ismap loop multiple muted novalidate readonly required reversed sandbox scoped selected accept="foo" accept-charset="foo" accesskey="foo" action="foo" align="foo" alt="foo" autocomplete="foo" bgcolor="foo" border="foo" charset="foo" cite="foo" class="foo" color="foo" cols="foo" colspan="foo" content="foo" contenteditable="foo" contextmenu="foo" coords="foo" data="foo" data="foo" datetime="foo" dir="foo" dirname="foo" download="foo" draggable="foo" dropzone="foo" enctype="foo" for="foo" form="foo" formaction="foo" headers="foo" height="foo" high="foo" href="foo" hreflang="foo" http="foo" id="foo" keytype="foo" kind="foo" label="foo" lang="foo" list="foo" low="foo" max="foo" maxlength="foo" media="foo" method="foo" min="foo" name="foo" onabort="foo" onafterprint="foo" onbeforeprint="foo" onbeforeunload="foo" onblur="foo" oncanplay="foo" oncanplaythrough="foo" onchange="foo" onclick="foo" oncontextmenu="foo" oncopy="foo" oncuechange="foo" oncut="foo" ondblclick="foo" ondrag="foo" ondragend="foo" ondragenter="foo" ondragleave="foo" ondragover="foo" ondragstart="foo" ondrop="foo" ondurationchange="foo" onemptied="foo" onended="foo" onerror="foo" onfocus="foo" onhashchange="foo" oninput="foo" oninvalid="foo" onkeydown="foo" onkeypress="foo" onkeyup="foo" onload="foo" onloadeddata="foo" onloadedmetadata="foo" onloadstart="foo" onmousedown="foo" onmousemove="foo" onmouseout="foo" onmouseover="foo" onmouseup="foo" onmousewheel="foo" onoffline="foo" ononline="foo" onpagehide="foo" onpageshow="foo" onpaste="foo" onpause="foo" onplay="foo" onplaying="foo" onpopstate="foo" onprogress="foo" onratechange="foo" onreset="foo" onresize="foo" onscroll="foo" onsearch="foo" onseeked="foo" onseeking="foo" onselect="foo" onshow="foo" onstalled="foo" onstorage="foo" onsubmit="foo" onsuspend="foo" ontimeupdate="foo" ontoggle="foo" onunload="foo" onvolumechange="foo" onwaiting="foo" onwheel="foo" open="foo" optimum="foo" pattern="foo" placeholder="foo" poster="foo" preload="foo" rel="foo" rows="foo" rowspan="foo" scope="foo" shape="foo" size="foo" sizes="foo" span="foo" spellcheck="foo" src="foo" srcdoc="foo" srclang="foo" srcset="foo" start="foo" step="foo" style="foo" tabindex="foo" target="foo" title="foo" translate="foo" type="foo" usemap="foo" value="foo" width="foo" wrap="foo"';
		$allowed_attributes = $all_attributes;

		return array(
			array('<a> with attributes', '<a ' . $all_attributes . '></a>', '&lt;a ' . $allowed_attributes . '&gt;&lt;/a&gt;'),
			array('<abbr> with attributes', '<abbr ' . $all_attributes . '></abbr>', '&lt;abbr ' . $allowed_attributes . '&gt;&lt;/abbr&gt;'),
			array('<acronym> with attributes', '<acronym ' . $all_attributes . '></acronym>', '&lt;acronym ' . $allowed_attributes . '&gt;&lt;/acronym&gt;'),
			array('<address> with attributes', '<address ' . $all_attributes . '></address>', '&lt;address ' . $allowed_attributes . '&gt;&lt;/address&gt;'),
			array('<applet> with attributes', '<applet ' . $all_attributes . '></applet>', '&lt;applet ' . $allowed_attributes . '&gt;&lt;/applet&gt;'),
			array('<area> with attributes', '<area ' . $all_attributes . '></area>', '&lt;area ' . $allowed_attributes . '&gt;&lt;/area&gt;'),
			array('<article> with attributes', '<article ' . $all_attributes . '></article>', '&lt;article ' . $allowed_attributes . '&gt;&lt;/article&gt;'),
			array('<aside> with attributes', '<aside ' . $all_attributes . '></aside>', '&lt;aside ' . $allowed_attributes . '&gt;&lt;/aside&gt;'),
			array('<audio> with attributes', '<audio ' . $all_attributes . '></audio>', '&lt;audio ' . $allowed_attributes .'&gt;&lt;/audio&gt;'),
			array('<b> with attributes', '<b ' . $all_attributes . '></b>', '&lt;b ' . $allowed_attributes . '&gt;&lt;/b&gt;'),
			array('<base> with attributes', '<base ' . $all_attributes . '></base>', '&lt;base ' . $allowed_attributes . '&gt;&lt;/base&gt;'),
			array('<basefont> with attributes', '<basefont ' . $all_attributes . '></basefont>', '&lt;basefont ' . $allowed_attributes . '&gt;&lt;/basefont&gt;'),
			array('<bdi> with attributes', '<bdi ' . $all_attributes . '></bdi>', '&lt;bdi ' . $allowed_attributes . '&gt;&lt;/bdi&gt;'),
			array('<bdo> with attributes', '<bdo ' . $all_attributes . '></bdo>', '&lt;bdo ' . $allowed_attributes . '&gt;&lt;/bdo&gt;'),
			array('<big> with attributes', '<big ' . $all_attributes . '></big>', '&lt;big ' . $allowed_attributes . '&gt;&lt;/big&gt;'),
			array('<blockquote> with attributes', '<blockquote ' . $all_attributes . '></blockquote>', '&lt;blockquote ' . $allowed_attributes . '&gt;&lt;/blockquote&gt;'),
			array('<body> with attributes', '<body ' . $all_attributes . '></body>', '&lt;body ' . $allowed_attributes . '&gt;&lt;/body&gt;'),
			array('<br> with attributes', '<br ' . $all_attributes . '>', '&lt;br ' . $allowed_attributes . '&gt;'),
			array('<button> with attributes', '<button ' . $all_attributes . '></button>', '&lt;button ' . $allowed_attributes . '&gt;&lt;/button&gt;'),
			array('<canvas> with attributes', '<canvas ' . $all_attributes . '></canvas>', '&lt;canvas ' . $allowed_attributes . '&gt;&lt;/canvas&gt;'),
			array('<caption> with attributes', '<caption ' . $all_attributes . '></caption>', '&lt;caption ' . $allowed_attributes . '&gt;&lt;/caption&gt;'),
			array('<center> with attributes', '<center ' . $all_attributes . '></center>', '&lt;center ' . $allowed_attributes . '&gt;&lt;/center&gt;'),
			array('<cite> with attributes', '<cite ' . $all_attributes . '></cite>', '&lt;cite ' . $allowed_attributes . '&gt;&lt;/cite&gt;'),
			array('<code> with attributes', '<code ' . $all_attributes . '></code>', '&lt;code ' . $allowed_attributes . '&gt;&lt;/code&gt;'),
			array('<col> with attributes', '<col ' . $all_attributes . '></col>', '&lt;col ' . $allowed_attributes . '&gt;&lt;/col&gt;'),
			array('<colgroup> with attributes', '<colgroup ' . $all_attributes . '></colgroup>', '&lt;colgroup ' . $allowed_attributes . '&gt;&lt;/colgroup&gt;'),
			array('<datalist> with attributes', '<datalist ' . $all_attributes . '></datalist>', '&lt;datalist ' . $allowed_attributes . '&gt;&lt;/datalist&gt;'),
			array('<dd> with attributes', '<dd ' . $all_attributes . '></dd>', '&lt;dd ' . $allowed_attributes . '&gt;&lt;/dd&gt;'),
			array('<del> with attributes', '<del ' . $all_attributes . '></del>', '&lt;del ' . $allowed_attributes . '&gt;&lt;/del&gt;'),
			array('<details> with attributes', '<details ' . $all_attributes . '></details>', '&lt;details ' . $allowed_attributes . '&gt;&lt;/details&gt;'),
			array('<dfn> with attributes', '<dfn ' . $all_attributes . '></dfn>', '&lt;dfn ' . $allowed_attributes . '&gt;&lt;/dfn&gt;'),
			array('<dialog> with attributes', '<dialog ' . $all_attributes . '></dialog>', '&lt;dialog ' . $allowed_attributes . '&gt;&lt;/dialog&gt;'),
			array('<dir> with attributes', '<dir ' . $all_attributes . '></dir>', '&lt;dir ' . $allowed_attributes . '&gt;&lt;/dir&gt;'),
			array('<div> with attributes', '<div ' . $all_attributes . '></div>', '&lt;div ' . $allowed_attributes . '&gt;&lt;/div&gt;'),
			array('<dl> with attributes', '<dl ' . $all_attributes . '></dl>', '&lt;dl ' . $allowed_attributes . '&gt;&lt;/dl&gt;'),
			array('<dt> with attributes', '<dt ' . $all_attributes . '></dt>', '&lt;dt ' . $allowed_attributes . '&gt;&lt;/dt&gt;'),
			array('<em> with attributes', '<em ' . $all_attributes . '></em>', '&lt;em ' . $allowed_attributes . '&gt;&lt;/em&gt;'),
			array('<embed> with attributes', '<embed ' . $all_attributes . '></embed>', '&lt;embed ' . $allowed_attributes . '&gt;&lt;/embed&gt;'),
			array('<fieldset> with attributes', '<fieldset ' . $all_attributes . '></fieldset>', '&lt;fieldset ' . $allowed_attributes . '&gt;&lt;/fieldset&gt;'),
			array('<figcaption> with attributes', '<figcaption ' . $all_attributes . '></figcaption>', '&lt;figcaption ' . $allowed_attributes . '&gt;&lt;/figcaption&gt;'),
			array('<figure> with attributes', '<figure ' . $all_attributes . '></figure>', '&lt;figure ' . $allowed_attributes . '&gt;&lt;/figure&gt;'),
			array('<font> with attributes', '<font ' . $all_attributes . '></font>', '&lt;font ' . $allowed_attributes . '&gt;&lt;/font&gt;'),
			array('<footer> with attributes', '<footer ' . $all_attributes . '></footer>', '&lt;footer ' . $allowed_attributes . '&gt;&lt;/footer&gt;'),
			array('<form> with attributes', '<form ' . $all_attributes . '></form>', '&lt;form ' . $allowed_attributes . '&gt;&lt;/form&gt;'),
			array('<frame> with attributes', '<frame ' . $all_attributes . '></frame>', '&lt;frame ' . $allowed_attributes . '&gt;&lt;/frame&gt;'),
			array('<frameset> with attributes', '<frameset ' . $all_attributes . '></frameset>', '&lt;frameset ' . $allowed_attributes . '&gt;&lt;/frameset&gt;'),
			array('<h1> with attributes', '<h1 ' . $all_attributes . '></h1>', '&lt;h1 ' . $allowed_attributes . '&gt;&lt;/h1&gt;'),
			array('<h2> with attributes', '<h2 ' . $all_attributes . '></h2>', '&lt;h2 ' . $allowed_attributes . '&gt;&lt;/h2&gt;'),
			array('<h3> with attributes', '<h3 ' . $all_attributes . '></h3>', '&lt;h3 ' . $allowed_attributes . '&gt;&lt;/h3&gt;'),
			array('<h4> with attributes', '<h4 ' . $all_attributes . '></h4>', '&lt;h4 ' . $allowed_attributes . '&gt;&lt;/h4&gt;'),
			array('<h5> with attributes', '<h5 ' . $all_attributes . '></h5>', '&lt;h5 ' . $allowed_attributes . '&gt;&lt;/h5&gt;'),
			array('<h6> with attributes', '<h6 ' . $all_attributes .'></h6>', '&lt;h6 ' . $allowed_attributes . '&gt;&lt;/h6&gt;'),
			array('<head> with attributes', '<head ' . $all_attributes . '></head>', '&lt;head ' . $allowed_attributes . '&gt;&lt;/head&gt;'),
			array('<header> with attributes', '<header ' . $all_attributes . '></header>', '&lt;header ' . $allowed_attributes . '&gt;&lt;/header&gt;'),
			array('<html> with attributes', '<html ' . $all_attributes . '></html>', '&lt;html ' . $allowed_attributes . '&gt;&lt;/html&gt;'),
			array('<i> with attributes', '<i ' . $all_attributes . '></i>', '&lt;i ' . $allowed_attributes . '&gt;&lt;/i&gt;'),
			array('<iframe> with attributes', '<iframe ' . $all_attributes . '></iframe>', '&lt;iframe ' . $allowed_attributes . '&gt;&lt;/iframe&gt;'),
			array('<img> with attributes', '<img ' . $all_attributes . '>', '&lt;img ' . $allowed_attributes . '&gt;'),
			array('<input> with attributes', '<input ' . $all_attributes . '>', '&lt;input ' . $allowed_attributes . '&gt;'),
			array('<ins> with attributes', '<ins ' . $all_attributes . '></ins>', '&lt;ins ' . $allowed_attributes . '&gt;&lt;/ins&gt;'),
			array('<kbd> with attributes', '<kbd ' . $all_attributes . '></kbd>', '&lt;kbd ' . $allowed_attributes . '&gt;&lt;/kbd&gt;'),
			array('<keygen> with attributes', '<keygen ' . $all_attributes . '></keygen>', '&lt;keygen ' . $allowed_attributes . '&gt;&lt;/keygen&gt;'),
			array('<label> with attributes', '<label ' . $all_attributes . '></label>', '&lt;label ' . $allowed_attributes . '&gt;&lt;/label&gt;'),
			array('<legend> with attributes', '<legend ' . $all_attributes . '></legend>', '&lt;legend ' . $allowed_attributes . '&gt;&lt;/legend&gt;'),
			array('<li> with attributes', '<li ' . $all_attributes . '></li>', '&lt;li ' . $allowed_attributes . '&gt;&lt;/li&gt;'),
			array('<link> with attributes', '<link ' . $all_attributes . '></link>', '&lt;link ' . $allowed_attributes . '&gt;&lt;/link&gt;'),
			array('<main> with attributes', '<main ' . $all_attributes . '></main>', '&lt;main ' . $allowed_attributes . '&gt;&lt;/main&gt;'),
			array('<map> with attributes', '<map ' . $all_attributes . '></map>', '&lt;map ' . $allowed_attributes . '&gt;&lt;/map&gt;'),
			array('<mark> with attributes', '<mark ' . $all_attributes . '></mark>', '&lt;mark ' . $allowed_attributes . '&gt;&lt;/mark&gt;'),
			array('<menu> with attributes', '<menu ' . $all_attributes . '></menu>', '&lt;menu ' . $allowed_attributes . '&gt;&lt;/menu&gt;'),
			array('<menuitem> with attributes', '<menuitem ' . $all_attributes . '></menuitem>', '&lt;menuitem ' . $allowed_attributes . '&gt;&lt;/menuitem&gt;'),
			array('<meta> with attributes', '<meta ' . $all_attributes . '></meta>', '&lt;meta ' . $allowed_attributes . '&gt;&lt;/meta&gt;'),
			array('<meter> with attributes', '<meter ' . $all_attributes . '></meter>', '&lt;meter ' . $allowed_attributes . '&gt;&lt;/meter&gt;'),
			array('<nav> with attributes', '<nav ' . $all_attributes . '></nav>', '&lt;nav ' . $allowed_attributes . '&gt;&lt;/nav&gt;'),
			array('<noframes> with attributes', '<noframes ' . $all_attributes . '></noframes>', '&lt;noframes ' . $allowed_attributes . '&gt;&lt;/noframes&gt;'),
			array('<noscript> with attributes', '<noscript ' . $all_attributes . '></noscript>', '&lt;noscript ' . $allowed_attributes . '&gt;&lt;/noscript&gt;'),
			array('<object> with attributes', '<object ' . $all_attributes . '></object>', '&lt;object ' . $allowed_attributes . '&gt;&lt;/object&gt;'),
			array('<ol> with attributes', '<ol ' . $all_attributes . '></ol>', '&lt;ol ' . $allowed_attributes . '&gt;&lt;/ol&gt;'),
			array('<optgroup> with attributes', '<optgroup ' . $all_attributes . '></optgroup>', '&lt;optgroup ' . $allowed_attributes . '&gt;&lt;/optgroup&gt;'),
			array('<option> with attributes', '<option ' . $all_attributes . '></option>', '&lt;option ' . $allowed_attributes . '&gt;&lt;/option&gt;'),
			array('<output> with attributes', '<output ' . $all_attributes . '></output>', '&lt;output ' . $allowed_attributes . '&gt;&lt;/output&gt;'),
			array('<p> with attributes', '<p ' . $all_attributes . '></p>', '&lt;p ' . $allowed_attributes . '&gt;&lt;/p&gt;'),
			array('<param> with attributes', '<param ' . $all_attributes . '></param>', '&lt;param ' . $allowed_attributes . '&gt;&lt;/param&gt;'),
			array('<picture> with attributes', '<picture ' . $all_attributes. '></picture>', '&lt;picture ' . $allowed_attributes . '&gt;&lt;/picture&gt;'),
			array('<pre> with attributes', '<pre ' . $all_attributes . '></pre>', '&lt;pre ' . $allowed_attributes . '&gt;&lt;/pre&gt;'),
			array('<progress> with attributes', '<progress ' . $all_attributes . '></progress>', '&lt;progress ' . $allowed_attributes . '&gt;&lt;/progress&gt;'),
			array('<q> with attributes', '<q ' . $all_attributes . '></q>', '&lt;q ' . $allowed_attributes . '&gt;&lt;/q&gt;'),
			array('<rp> with attributes', '<rp ' . $all_attributes . '></rp>', '&lt;rp ' . $allowed_attributes . '&gt;&lt;/rp&gt;'),
			array('<rt> with attributes', '<rt ' . $all_attributes . '></rt>', '&lt;rt ' . $allowed_attributes . '&gt;&lt;/rt&gt;'),
			array('<ruby> with attributes', '<ruby ' . $all_attributes . '></ruby>', '&lt;ruby ' . $allowed_attributes . '&gt;&lt;/ruby&gt;'),
			array('<s> with attributes', '<s ' . $all_attributes . '></s>', '&lt;s ' . $allowed_attributes . '&gt;&lt;/s&gt;'),
			array('<samp> with attributes', '<samp ' . $all_attributes . '></samp>', '&lt;samp ' . $allowed_attributes . '&gt;&lt;/samp&gt;'),
			array('<script> with attributes', '<script ' . $all_attributes . '></script>', '&lt;script ' . $allowed_attributes . '&gt;&lt;/script&gt;'),
			array('<section> with attributes', '<section ' . $all_attributes . '></section>', '&lt;section ' . $allowed_attributes . '&gt;&lt;/section&gt;'),
			array('<select> with attributes', '<select ' . $all_attributes . '></select>', '&lt;select ' . $allowed_attributes . '&gt;&lt;/select&gt;'),
			array('<small> with attributes', '<small ' . $all_attributes . '></small>', '&lt;small ' . $allowed_attributes . '&gt;&lt;/small&gt;'),
			array('<source> with attributes', '<source ' . $all_attributes . '></source>', '&lt;source ' . $allowed_attributes . '&gt;&lt;/source&gt;'),
			array('<span> with attributes', '<span ' . $all_attributes . '></span>', '&lt;span ' . $allowed_attributes . '&gt;&lt;/span&gt;'),
			array('<strike> with attributes', '<strike ' . $all_attributes . '></strike>', '&lt;strike ' . $allowed_attributes . '&gt;&lt;/strike&gt;'),
			array('<strong> with attributes', '<strong ' . $all_attributes . '></strong>', '&lt;strong ' . $allowed_attributes . '&gt;&lt;/strong&gt;'),
			array('<style> with attributes', '<style ' . $all_attributes . '></style>', '&lt;style ' . $allowed_attributes . '&gt;&lt;/style&gt;'),
			array('<sub> with attributes', '<sub ' . $all_attributes . '></sub>', '&lt;sub ' . $allowed_attributes . '&gt;&lt;/sub&gt;'),
			array('<summary> with attributes', '<summary ' . $all_attributes . '></summary>', '&lt;summary ' . $allowed_attributes . '&gt;&lt;/summary&gt;'),
			array('<sup> with attributes', '<sup ' . $all_attributes . '></sup>', '&lt;sup ' . $allowed_attributes . '&gt;&lt;/sup&gt;'),
			array('<table> with attributes', '<table ' . $all_attributes . '></table>', '&lt;table ' . $allowed_attributes . '&gt;&lt;/table&gt;'),
			array('<tbody> with attributes', '<tbody ' . $all_attributes . '></tbody>', '&lt;tbody ' . $allowed_attributes . '&gt;&lt;/tbody&gt;'),
			array('<td> with attributes', '<td ' . $all_attributes . '></td>', '&lt;td ' . $allowed_attributes . '&gt;&lt;/td&gt;'),
			array('<textarea> with attributes', '<textarea ' . $all_attributes . '></textarea>', '&lt;textarea ' . $allowed_attributes . '&gt;&lt;/textarea&gt;'),
			array('<tfoot> with attributes', '<tfoot ' . $all_attributes . '></tfoot>', '&lt;tfoot ' . $allowed_attributes . '&gt;&lt;/tfoot&gt;'),
			array('<th> with attributes', '<th ' . $all_attributes . '></th>', '&lt;th ' . $allowed_attributes . '&gt;&lt;/th&gt;'),
			array('<thead> with attributes', '<thead ' . $all_attributes . '></thead>', '&lt;thead ' . $allowed_attributes . '&gt;&lt;/thead&gt;'),
			array('<time> with attributes', '<time ' . $all_attributes . '></time>', '&lt;time ' . $allowed_attributes . '&gt;&lt;/time&gt;'),
			array('<title> with attributes', '<title ' . $all_attributes . '></title>', '&lt;title ' . $allowed_attributes . '&gt;&lt;/title&gt;'),
			array('<tr> with attributes', '<tr ' . $all_attributes . '></tr>', '&lt;tr ' . $allowed_attributes . '&gt;&lt;/tr&gt;'),
			array('<track> with attributes', '<track ' . $all_attributes . '></track>', '&lt;track ' . $allowed_attributes . '&gt;&lt;/track&gt;'),
			array('<tt> with attributes', '<tt ' . $all_attributes . '></tt>', '&lt;tt ' . $allowed_attributes . '&gt;&lt;/tt&gt;'),
			array('<u> with attributes', '<u ' . $all_attributes . '></u>', '&lt;u ' . $allowed_attributes . '&gt;&lt;/u&gt;'),
			array('<ul> with attributes', '<ul ' . $all_attributes . '></ul>', '&lt;ul ' . $allowed_attributes . '&gt;&lt;/ul&gt;'),
			array('<var> with attributes', '<var ' . $all_attributes . '></var>', '&lt;var ' . $allowed_attributes . '&gt;&lt;/var&gt;'),
			array('<video> with attributes', '<video ' . $all_attributes . '></video>', '&lt;video ' . $allowed_attributes . '&gt;&lt;/video&gt;'),
			array('<wbr> with attributes', '<wbr ' . $all_attributes . '></wbr>', '&lt;wbr ' . $allowed_attributes . '&gt;&lt;/wbr&gt;'),
		);
	}

	protected function markdownData()
	{
		$data = array(
			// Automatic Escaping
			array('Ampersands', 'AT&T', "<p>AT&amp;T</p>\n"),
			array('HTML Entity', '&copy;', "<p>&copy;</p>\n"),
			array('Angle brackets', '4 < 5 and 3 > 4', "<p>4 &lt; 5 and 3 > 4</p>\n"),

			// Links
			// array('Link with title attribute', 'This is [an example](http://example.com/ "Title") inline link.', ""),
			array('Link without title attribute', '[This link](http://example.net/) has no title attribute.', "<p><a href=\"http://example.net/\">This link</a> has no title attribute.</p>\n"),
			array('Relative URLs', 'See my [About](/about/) page for details.', "<p>See my <a href=\"/about/\">About</a> page for details.</p>\n"),
			array('Refernce style link', "This is [an example][id] reference-style link.\n\n[id]: http://example.com/  \"Optional Title Here\"\n", "<p>This is <a href=\"http://example.com/\" title=\"Optional Title Here\">an example</a> reference-style link.</p>\n"),
			array('Refernce style link (with space)', "This is [an example] [id] reference-style link.\n\n[id]: http://example.com/  \"Optional Title Here\"\n", "<p>This is <a href=\"http://example.com/\" title=\"Optional Title Here\">an example</a> reference-style link.</p>\n"),
			// array('Refernce style link (with single quote)', "This is [an example][id] reference-style link.\n\n[id]: http://example.com/  'Optional Title Here'", "<p>This is <a href=\"http://example.com/\" title=\"Optional Title Here\">an example</a> reference-style link.</p>\n"),
			array('Refernce style link (with parenthesis)', "This is [an example][id] reference-style link.\n\n[id]: http://example.com/  (Optional Title Here)", "<p>This is <a href=\"http://example.com/\" title=\"Optional Title Here\">an example</a> reference-style link.</p>\n"),
			array('Refernce style link (with angle brackets)', "This is [an example][id] reference-style link.\n\n[id]: <http://example.com/>  \"Optional Title Here\"\n", "<p>This is <a href=\"http://example.com/\" title=\"Optional Title Here\">an example</a> reference-style link.</p>\n"),
			array('Refernce style link (with title on newline)', "This is [an example][id] reference-style link.\n\n[id]: http://example.com/\n\t\"Optional Title Here\"\n", "<p>This is <a href=\"http://example.com/\" title=\"Optional Title Here\">an example</a> reference-style link.</p>\n"),
			array('Refernce style implicit link', "Visit [Daring Fireball][] for more information.\n\n[Daring Fireball]: http://daringfireball.net/", "<p>Visit <a href=\"http://daringfireball.net/\">Daring Fireball</a> for more information.</p>\n"),
			array('Automatic link', '<http://example.com>', '<p><a href="http://example.com">http://example.com</a></p>' . "\n"),
			array('Automatic email ink', '<address@example.com>', '<p><a href="&#109;&#x61;&#x69;&#108;&#x74;&#x6f;&#58;&#x61;&#x64;&#100;&#114;&#x65;&#115;&#115;&#x40;&#101;&#120;&#x61;m&#112;&#x6c;e&#46;&#x63;&#x6f;&#109;">&#x61;&#x64;&#100;&#114;&#x65;&#115;&#115;&#x40;&#101;&#120;&#x61;m&#112;&#x6c;e&#46;&#x63;&#x6f;&#109;</a></p>' . "\n"),

			// Emphasis
			array('Single asterisks', '*single asterisks*', "<p><em>single asterisks</em></p>\n"),
			array('Single underscores', '_single underscores_', "<p><em>single underscores</em></p>\n"),
			array('Double asterisks', '**double asterisks**', "<p><strong>double asterisks</strong></p>\n"),
			array('Double underscores', '__double underscores__', "<p><strong>double underscores</strong></p>\n"),
			array('Single asterisks in the middle of a word', 'un*frigging*believable', "<p>un<em>frigging</em>believable</p>\n"),
			array('Double asterisks in the middle of a word', 'un**frigging**believable', "<p>un<strong>frigging</strong>believable</p>\n"),
			array('Literal asterisk', '8 * 7 = 56', "<p>8 * 7 = 56</p>\n"),
			array('Literal underscore', 'Literal _ underscore', "<p>Literal _ underscore</p>\n"),
			array('Escaped asterisk', '\*this text is surrounded by literal asterisks\*', "<p>&#42;this text is surrounded by literal asterisks&#42;</p>\n"),
			array('Escaped underscore', '\_this text is surrounded by literal asterisks\_', "<p>&#95;this text is surrounded by literal asterisks&#95;</p>\n"),

			// Code
			array('Span of code', 'Use the `printf()` function.', "<p>Use the <code>printf()</code> function.</p>\n"),
			array('Literal backtick', '``There is a literal backtick (`) here.``', "<p><code>There is a literal backtick (`) here.</code></p>\n"),
			array('Encoded angle brackets inside code span', "Please don't use any `<blink>` tags.", "<p>Please don&#8217;t use any <code>&lt;blink&gt;</code> tags.</p>\n"),
			array('Encoded ampersands inside code span', '`&#8212;` is the decimal-encoded equivalent of `&mdash;`.', "<p><code>&amp;#8212;</code> is the decimal-encoded equivalent of <code>&amp;mdash;</code>.</p>\n"),

			// Images
			array('Image tag', '![Alt text](/path/to/img.jpg)', "<p><img src=\"/path/to/img.jpg\" alt=\"Alt text\" /></p>\n"),
			// array('Image tag with title', '![Alt text](/path/to/img.jpg "Optional title")', "\n"),
			array('Reference style image tag', "![Alt text][id]\n\n[id]: url/to/image  \"Optional title attribute\"\n", "<p><img src=\"url/to/image\" alt=\"Alt text\" title=\"Optional title attribute\" /></p>\n"),

			// Escapes
			array('Escaped backslash', '\\\\', "<p>&#92;</p>\n"),
			array('Escaped backtick', '\`', "<p>&#96;</p>\n"),
			array('Escaped asterisk', '\*', "<p>&#42;</p>\n"),
			array('Escaped underscore', '\_', "<p>&#95;</p>\n"),
			array('Escaped curly braces', '\{\}', "<p>&#123;&#125;</p>\n"),
			array('Escaped square brackets', '\[\]', "<p>&#91;&#93;</p>\n"),
			array('Escaped parentheses', '\(\)', "<p>&#40;&#41;</p>\n"),
			array('Escaped hash mark', '\#', "<p>&#35;</p>\n"),
			array('Escaped plus sign', '\+', "<p>&#43;</p>\n"),
			array('Escaped hyphen', '\-', "<p>&#45;</p>\n"),
			array('Escaped dot', '\.', "<p>&#46;</p>\n"),
			array('Escaped exclamation mark', '\!', "<p>&#33;</p>\n"),

			// Horizontal rules
			array('HR by "* * *"', '* * *', "<hr />\n"),
			array('HR by "***"', '***', "<hr />\n"),
			array('HR by "*****"', '*****', "<hr />\n"),
			array('HR by "---"', '---', "<hr />\n"),
			array('HR by "- - -"', '- - -', "<hr />\n"),
			array('HR by "-----"', '-----', "<hr />\n"),
			array('HR by "___"', '___', "<hr />\n"),
			array('HR by "_ _ _"', '_ _ _', "<hr />\n"),
			array('HR by "_____"', '_____', "<hr />\n"),

			// Headers
			array('# H1', '# This is an H1', "<h1>This is an H1</h1>\n"),
			array('## H2', '## This is an H2', "<h2>This is an H2</h2>\n"),
			array('### H3', '### This is an H3', "<h3>This is an H3</h3>\n"),
			array('#### H4', '#### This is an H4', "<h4>This is an H4</h4>\n"),
			array('##### H5', '##### This is an H5', "<h5>This is an H5</h5>\n"),
			array('###### H6', '###### This is an H6', "<h6>This is an H6</h6>\n"),
			array('# H1 #', '# This is an H1 #', "<h1>This is an H1</h1>\n"),
			array('## H2 ##', '## This is an H2 ##', "<h2>This is an H2</h2>\n"),
			array('### H3 ###', '### This is an H3 ###', "<h3>This is an H3</h3>\n"),
			array('#### H4 ####', '#### This is an H4 ####', "<h4>This is an H4</h4>\n"),
			array('##### H5 #####', '##### This is an H5 #####', "<h5>This is an H5</h5>\n"),
			array('###### H6 ######', '###### This is an H6 ######', "<h6>This is an H6</h6>\n"),
			array('H1 by underscore', "This is an H1\n=============", "<h1>This is an H1</h1>\n"),
			array('H2 by underscore', "This is an H2\n-------------", "<h2>This is an H2</h2>\n"),

			// Blockquotes
			array('Email style blockquote', $this->getContentForMarkup('email-style-blockquote.in.md'), $this->getContentForMarkup('email-style-blockquote.out.md')),
			array('Lazy style blockquote', $this->getContentForMarkup('lazy-style-blockquote.in.md'), $this->getContentForMarkup('lazy-style-blockquote.out.md')),
			array('Nested blockquotes', $this->getContentForMarkup('nested-blockquotes.in.md'), $this->getContentForMarkup('nested-blockquotes.out.md')),
			array('Markdown inside blockquote', $this->getContentForMarkup('markdown-in-blockquote.in.md'), $this->getContentForMarkup('markdown-in-blockquote.out.md')),

			// Lists
			array('List by asterisk', $this->getContentForMarkup('list-by-asterisk.in.md'), $this->getContentForMarkup('list-by-asterisk.out.md')),
			array('List by plus', $this->getContentForMarkup('list-by-plus.in.md'), $this->getContentForMarkup('list-by-plus.out.md')),
			array('List by hyphen', $this->getContentForMarkup('list-by-hyphen.in.md'), $this->getContentForMarkup('list-by-hyphen.out.md')),
			array('Ordered list', $this->getContentForMarkup('ordered-list.in.md'), $this->getContentForMarkup('ordered-list.out.md')),
			array('Wrapped lists', $this->getContentForMarkup('wrapped-lists.in.md'), $this->getContentForMarkup('wrapped-lists.out.md')),
			array('Lists with paragraph tags', $this->getContentForMarkup('lists-with-paragraph-tags.in.md'), $this->getContentForMarkup('lists-with-paragraph-tags.out.md')),
			array('Lists with paragraphs', $this->getContentForMarkup('lists-with-paragraphs.in.md'), $this->getContentForMarkup('lists-with-paragraphs.out.md')),
			array('Lists with blockquotes', $this->getContentForMarkup('lists-with-blockquotes.in.md'), $this->getContentForMarkup('lists-with-blockquotes.out.md')),
			array('Lists with code blocks', $this->getContentForMarkup('lists-with-code-blocks.in.md'), $this->getContentForMarkup('lists-with-code-blocks.out.md')),
			array('Not a list', '1986\. What a great season.', "<p>1986&#46; What a great season.</p>\n"),

			// Code blocks
			array('Code block by 4 spaces', $this->getContentForMarkup('codeblock-by-4-spaces.in.md'), $this->getContentForMarkup('codeblock-by-4-spaces.out.md')),
			array('Code block by 5 spaces', $this->getContentForMarkup('codeblock-by-5-spaces.in.md'), $this->getContentForMarkup('codeblock-by-5-spaces.out.md')),
			array('Code block by 1 tab', $this->getContentForMarkup('codeblock-by-1-tab.in.md'), $this->getContentForMarkup('codeblock-by-1-tab.out.md')),
			array('Code block by 2 tabs', $this->getContentForMarkup('codeblock-by-2-tabs.in.md'), $this->getContentForMarkup('codeblock-by-2-tabs.out.md')),
			array('Code block with encoded ampersands', $this->getContentForMarkup('codeblock-with-encoded-ampersands.in.md'), $this->getContentForMarkup('codeblock-with-encoded-ampersands.out.md')),
			array('Code block with encoded angle brackets', $this->getContentForMarkup('codeblock-with-encoded-angle-brackets.in.md'), $this->getContentForMarkup('codeblock-with-encoded-angle-brackets.out.md')),
		);

		return array_merge($data, $this->markdownExtraData());
	}

	protected function markdownExtraData()
	{
		return array(
			array('Markdown inside HTML blocks', $this->getContentForMarkup('markdown-inside-html-blocks.in.md'), $this->getContentForMarkup('markdown-inside-html-blocks.out.md')),

			// Special Attributes
			array('Header: ID', '## Header 2 {#header2}', "<h2 id=\"header2\">Header 2</h2>\n"),
			array('Header: Class names', '## Header 2 {.main}', "<h2 class=\"main\">Header 2</h2>\n"),
			array('Header: Custom attributes', '## Le Header 2 {lang=fr}', "<h2 lang=\"fr\">Le Header 2</h2>\n"),
			array('Header: Multiple attributes', '## Le Header 2 {.main .shine lang=fr #header2}', "<h2 id=\"header2\" class=\"main shine\" lang=\"fr\">Le Header 2</h2>\n"),
			array('Link: ID', '[link](/url){#header2}', "<p><a href=\"/url\" title=\"\" id=\"header2\">link</a></p>\n"),
			array('Link: Class names', '[link](/url){.main}', "<p><a href=\"/url\" title=\"\" class=\"main\">link</a></p>\n"),
			array('Link: Custom attributes', '[link](/url){lang=fr}', "<p><a href=\"/url\" title=\"\" lang=\"fr\">link</a></p>\n"),
			array('Link: Multiple attributes', '[link](/url){.main .shine lang=fr #header2}', "<p><a href=\"/url\" title=\"\" id=\"header2\" class=\"main shine\" lang=\"fr\">link</a></p>\n"),
			array('Reference Link: ID', "[link][linkref]\n\n[linkref]: /url {#header2}", "<p><a href=\"/url\" title=\"\" id=\"header2\">link</a></p>\n"),
			array('Reference Link: Class names', "[link][linkref]\n\n[linkref]: /url {.main}", "<p><a href=\"/url\" title=\"\" class=\"main\">link</a></p>\n"),
			array('Reference Link: Custom attributes', "[link][linkref]\n\n[linkref]: /url {lang=fr}", "<p><a href=\"/url\" title=\"\" lang=\"fr\">link</a></p>\n"),
			array('Reference Link: Multiple attributes', "[link][linkref]\n\n[linkref]: /url {.main .shine lang=fr #header2}", "<p><a href=\"/url\" title=\"\" id=\"header2\" class=\"main shine\" lang=\"fr\">link</a></p>\n"),
			array('Image: ID', '![link](/url){#header2}', "<p><img src=\"/url\" alt=\"link\" title=\"\" id=\"header2\" /></p>\n"),
			array('Image: Class names', '![link](/url){.main}', "<p><img src=\"/url\" alt=\"link\" title=\"\" class=\"main\" /></p>\n"),
			array('Image: Custom attributes', '![link](/url){lang=fr}', "<p><img src=\"/url\" alt=\"link\" title=\"\" lang=\"fr\" /></p>\n"),
			array('Image: Multiple attributes', '![link](/url){.main .shine lang=fr #header2}', "<p><img src=\"/url\" alt=\"link\" title=\"\" id=\"header2\" class=\"main shine\" lang=\"fr\" /></p>\n"),
			array('Fenced code block: ID', "~~~ {#header2}\ncode block\n~~~\n", "<pre><code id=\"header2\">code block\n</code></pre>\n"),
			array('Fenced code block: Class names', "~~~ {.main}\ncode block\n~~~\n", "<pre><code class=\"main\">code block\n</code></pre>\n"),
			array('Fenced code block: Custom attributes', "~~~ {lang=fr}\ncode block\n~~~\n", "<pre><code lang=\"fr\">code block\n</code></pre>\n"),
			array('Fenced code block: Multiple attributes', "~~~ {.main .shine lang=fr #header2}\ncode block\n~~~\n", "<pre><code id=\"header2\" class=\"main shine\" lang=\"fr\">code block\n</code></pre>\n"),

			// Fenced Code Blocks
			array('Fenced code block by 3 tildes', $this->getContentForMarkup('codeblock-by-3-tildes.in.md'), $this->getContentForMarkup('codeblock-by-3-tildes.out.md')),
			array('Fenced code block by 4 tildes', $this->getContentForMarkup('codeblock-by-4-tildes.in.md'), $this->getContentForMarkup('codeblock-by-4-tildes.out.md')),
			array('Fenced code block by 3 backticks', $this->getContentForMarkup('codeblock-by-3-backticks.in.md'), $this->getContentForMarkup('codeblock-by-3-backticks.out.md')),
			array('Fenced code block by 4 backticks', $this->getContentForMarkup('codeblock-by-4-backticks.in.md'), $this->getContentForMarkup('codeblock-by-4-backticks.out.md')),
			array('Fenced code block beginning and ending with blank lines', $this->getContentForMarkup('codeblock-begin-end-blank-lines.in.md'), $this->getContentForMarkup('codeblock-begin-end-blank-lines.out.md')),
			array('Fenced code block after a list', $this->getContentForMarkup('codeblock-after-list.in.md'), $this->getContentForMarkup('codeblock-after-list.out.md')),

			// Tables
			array('Table', $this->getContentForMarkup('table.in.md'), $this->getContentForMarkup('table.out.md')),
			array('Table with leading and tailing pipes', $this->getContentForMarkup('table-with-pipes.in.md'), $this->getContentForMarkup('table-with-pipes.out.md')),
			array('Table with alignment', $this->getContentForMarkup('table-with-alignment.in.md'), $this->getContentForMarkup('table-with-alignment.out.md')),
			array('Table with span-level formatting', $this->getContentForMarkup('table-with-formatting.in.md'), $this->getContentForMarkup('table-with-formatting.out.md')),

			// Definition Lists
			array('Definition list', $this->getContentForMarkup('definition-list.in.md'), $this->getContentForMarkup('definition-list.out.md')),
			array('Definition list wrapped', $this->getContentForMarkup('definition-list-wrapped.in.md'), $this->getContentForMarkup('definition-list-wrapped.out.md')),
			array('Definition list indented', $this->getContentForMarkup('definition-list-indented.in.md'), $this->getContentForMarkup('definition-list-indented.out.md')),
			array('Definition list multiple definitions', $this->getContentForMarkup('definition-list-multiple.in.md'), $this->getContentForMarkup('definition-list-multiple.out.md')),
			array('Definition list with paragraph tags', $this->getContentForMarkup('definition-list-with-paragraph-tags.in.md'), $this->getContentForMarkup('definition-list-with-paragraph-tags.out.md')),
			array('Definition list with multiple paragraphs', $this->getContentForMarkup('definition-list-with-multiple-paragraphs.in.md'), $this->getContentForMarkup('definition-list-with-multiple-paragraphs.out.md')),

			// Footnotes
			array('Footnote first', $this->getContentForMarkup('footnote-first.in.md'), $this->getContentForMarkup('footnote-first.out.md')),
			array('Footnote intermingled', $this->getContentForMarkup('footnote-intermingled.in.md'), $this->getContentForMarkup('footnote-intermingled.out.md')),
			array('Footnote last', $this->getContentForMarkup('footnote-last.in.md'), $this->getContentForMarkup('footnote-last.out.md')),
			array('Footnote with multiple paragraphs', $this->getContentForMarkup('footnote-with-multiple-paragraphs.in.md'), $this->getContentForMarkup('footnote-with-multiple-paragraphs.out.md')),

			// Abbreviations
			array('Abbreviation definition first', "*[HTML]: Hyper Text Markup Language\n\nThe HTML specification", "<p>The <abbr title=\"Hyper Text Markup Language\">HTML</abbr> specification</p>\n"),
			array('Abbreviation definition middle', "The HTML specification\n\n*[HTML]: Hyper Text Markup Language\n\nis dry", "<p>The <abbr title=\"Hyper Text Markup Language\">HTML</abbr> specification</p>\n\n<p>is dry</p>\n"),
			array('Abbreviation definition last', "The HTML specification\n\n*[HTML]: Hyper Text Markup Language", "<p>The <abbr title=\"Hyper Text Markup Language\">HTML</abbr> specification</p>\n"),
			array('Multiword abbreviation', "*[Foo Bar]: Fubar\n\nI saw a Foo Bar once", "<p>I saw a <abbr title=\"Fubar\">Foo Bar</abbr> once</p>\n"),
			array('Empty abbreviateion', "Operation Tigra Genesis is going well.\n\n*[Tigra Genesis]:", "<p>Operation <abbr>Tigra Genesis</abbr> is going well.</p>\n"),

			array('Single underscores in the middle of a word', 'un_frigging_believable', "<p>un_frigging_believable</p>\n"),
			array('Double underscores in the middle of a word', 'un__frigging__believable', "<p>un__frigging__believable</p>\n"),
		);
	}

	protected function BBCodeData()
	{
		return array(
            array('[abbr] tag', '[abbr="Cascading Style Sheets"]CSS[/abbr]', '<abbr title="Cascading Style Sheets">CSS</abbr>'),
            array('[abbr] tag with property', '[abbr title="Cascading Style Sheets"]CSS[/abbr]', '<abbr title="Cascading Style Sheets">CSS</abbr>'),
            array('[b] tag', '[b]some bold text[/b]', '<b>some bold text</b>'),
            array('[blockquote] tag', '[blockquote]Some text. blah, blah, blah...[/blockquote]', '<blockquote>Some text. blah, blah, blah...</blockquote>'),
            // array('[cite] tag', '[cite="some place"]some text[/cite]', ''),
            // array('[code] tag', '[code]Some pre-formatted text...[/code]', '<code>Some pre-formatted text...</code>'),
            array('[color] tag', '[color=green]Some green text[/color]', '<span style="color:green;">Some green text</span>'),
            array('[del] tag', 'This is [del]very[/del] exciting.', 'This is <del>very</del> exciting.'),
            array('[em] tag', '[em]some em text[/em]', '<em>some em text</em>'),
            // array('[email] tag', '[email]you@example.com[/email]', "<span data-eeEncEmail_qqaBvHJmAg='1'>.encoded_email</span><script type=\"text/javascript\">/*<![CDATA[*/var out = '',el = document.getElementsByTagName('span'),l = ['>','a','/','<',' 109',' 111',' 99',' 46',' 101',' 108',' 112',' 109',' 97',' 120',' 101',' 64',' 117',' 111',' 121','>','\"',' 109',' 111',' 99',' 46',' 101',' 108',' 112',' 109',' 97',' 120',' 101',' 64',' 117',' 111',' 121',':','o','t','l','i','a','m','\\\"','=','f','e','r','h','a ','<'],i = l.length,j = el.length;while (--i >= 0)out += unescape(l[i].replace(/^\s\s*/, '&#'));while (--j >= 0)if (el[j].getAttribute('data-eeEncEmail_qqaBvHJmAg'))el[j].innerHTML = out;/*]]>*/</script>"),
            // array('[email] tag with value', '[email=you@example.com]click here to email[/email]', ''),
            array('[h2] tag', '[h2]some text[/h2]', '<h2>some text</h2>'),
            array('[h3] tag', '[h3]some text[/h3]', '<h3>some text</h3>'),
            array('[h4] tag', '[h4]some text[/h4]', '<h4>some text</h4>'),
            array('[h5] tag', '[h5]some text[/h5]', '<h5>some text</h5>'),
            array('[h6] tag', '[h6]some text[/h6]', '<h6>some text</h6>'),
            array('[i] tag', '[i]some italic text[/i]', '<i>some italic text</i>'),
            // array('[img] tag', '[img]http://example.com/pic.jpg[/img]', ''),
            array('[ins] tag', '[ins]some text[/ins]', '<ins>some text</ins>'),
            array('[mark] tag', '[mark]some text[/mark]', '<mark>some text</mark>'),
            array('[pre] tag', '[pre]Some pre-formatted text...[/pre]', '<pre>Some pre-formatted text...</pre>'),
            array('[quote] tag', '[quote]Some text. blah, blah, blah...[/quote]', '<blockquote>Some text. blah, blah, blah...</blockquote>'),
            array('[size=1] tag', '[size=1]some text[/size]', '<span style="font-size:9px;">some text</span>'),
            array('[size=2] tag', '[size=2]some text[/size]', '<span style="font-size:11px;">some text</span>'),
            array('[size=3] tag', '[size=3]some text[/size]', '<span style="font-size:14px;">some text</span>'),
            array('[size=4] tag', '[size=4]some text[/size]', '<span style="font-size:16px;">some text</span>'),
            array('[size=5] tag', '[size=5]some text[/size]', '<span style="font-size:18px;">some text</span>'),
            array('[size=6] tag', '[size=6]some text[/size]', '<span style="font-size:20px;">some text</span>'),
            array('[span] tag', '[span]some text[/span]', '<span>some text</span>'),
            array('[strike] tag', '[strike]some text[/strike]', '<del>some text</del>'),
            array('[strong] tag', '[strong]some strong text[/strong]', '<strong>some strong text</strong>'),
            array('[style] tag', '[style=class_name]your content[/style]', '<span class="class_name">your content</span>'),
            array('[sub] tag', '[sub]some text[/sub]', '<sub>some text</sub>'),
            array('[sup] tag', '[sup]some text[/sup]', '<sup>some text</sup>'),
            array('[u] tag', '[u]some underlined text[/u]', '<em>some underlined text</em>'),
            array('[url] tag', '[url]http://example.com/[/url]', '<a href="http://example.com/">http://example.com/</a>'),
            array('[url] tag with value', '[url=http://example.com/]my site[/url]', '<a href="http://example.com/">my site</a>'),
            // array('[url] tag with value and property', '[url=http://example.com/ class="link"]my site[/url]', '<a href="http://example.com/" class="link">my site</a>'),
		);
	}

}

class ConfigStub {
	public function item($str = '')
	{
		return 'n';
	}
}

class LoadStub {
	public function model($str = '')
	{
		return;
	}

	public function helper($str = '')
	{
		return;
	}
}

class AddonsModelStub {
	public function get_plugin_formatting()
	{
		return array();
	}
}

class ExtensionsStub {
	public function active_hook($str)
	{
		return FALSE;
	}
}

function ee($str = '')
{
	if ($str)
	{
		require_once SYSPATH.'ee/EllisLab/ExpressionEngine/Library/Security/XSS.php';
		return new EllisLab\ExpressionEngine\Library\Security\XSS();
	}

	$obj = new StdClass();
	$obj->config = new ConfigStub();
	$obj->load = new LoadStub();
	$obj->addons_model = new AddonsModelStub();
	$obj->functions = new EE_Functions();
	$obj->extensions = new ExtensionsStub();

	return $obj;
}

// EOF
