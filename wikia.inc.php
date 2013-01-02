<?php
	/* 
	 * @package     Wikia - Wiki Parser
	 * @author      Sajjad Rad
	 * @copyright   Sajjad Rad 2013
	 * @link        https://github.com/sajjadrad/Wikia/
	 * @version     {SUBVERSION_BUILD_NUMBER}
	 * 
	 * @licence     MIT
	 */
	function getPartBetween($str, $a, $b)
	{
		$start = strpos($str,$a) + strlen($a);
		if(strpos($str,$a) === false) return false;
		$length = strpos($str,$b,$start) - $start;
		if(strpos($str,$b,$start) === false) return false;
		return substr($str,$start,$length);
	}
	function debug_preg($matches)
	{
		echo "\n\n<h3 style='color=red'>PREG</h3><pre>\n\n";
		var_dump($matches);
		echo "\n\n<hr style='color=red' />n\n";
		return $matches[0];
	}
	function generateMap($html)
	{
		$html="\n\n".$html;
		$out;
		$tempArray;
		$l=0;
		for($i=1;$i<8;$i++)
		{
			preg_match_all('/\n+[=]{'.$i.'}([^=]+)[=]{'.$i.'}\n*/',$html,$out, PREG_OFFSET_CAPTURE);
			for($k=0;$k<count($out[1]);$k++)
			{
				$tempArray[$l++] = array(
					"Title"=>$out[1][$k][0],
					"CharIndex"=>$out[1][$k][1],
					"Index"=>$i
					);
			}	
		}
		for($m=0;$m<count($tempArray)-1;$m++)
		{
			for($n=0;$n<count($tempArray)-1;$n++)
			{
				if($tempArray[$n]['CharIndex']>$tempArray[$n+1]['CharIndex'])
				{
					//Swap array elements
					$tempTitle = $tempArray[$n]['Title'];
					$tempCharIndex = $tempArray[$n]['CharIndex'];
					$tempIndex = $tempArray[$n]['Index'];
					
					$tempArray[$n]['Title'] = $tempArray[$n+1]['Title'];
					$tempArray[$n]['CharIndex'] = $tempArray[$n+1]['CharIndex'];
					$tempArray[$n]['Index'] = $tempArray[$n+1]['Index'];
					
					$tempArray[$n+1]['Title'] = $tempTitle;
					$tempArray[$n+1]['CharIndex'] = $tempCharIndex;
					$tempArray[$n+1]['Index'] = $tempIndex;
				}
			}
		}
		$map="<ul>";
		$headerFlag=False; //First Header Flag
		$tempTitle="";
		$nowIndex=0;
		$pastIndex=0;
		$mainHeaderIndex=7; //Default main header index
		//Sort array
		for($m=0 ; $m < count($tempArray) ; $m++)
		{
			$nowIndex=$tempArray[$m]['Index'];
			
			if($nowIndex<$mainHeaderIndex)
				$mainHeaderIndex=$nowIndex;
			if($headerFlag)
			{
				if($nowIndex>$pastIndex)
				{
					$map=$map."<li>".$tempTitle."<ul>";
					$pastIndex=$nowIndex;
					$tempTitle=$tempArray[$m]['Title'];
				}
				else if($nowIndex==$pastIndex)
				{
					$map=$map."<li>".$tempTitle."</li>";
					$pastIndex=$nowIndex;
					$tempTitle=$tempArray[$m]['Title'];
				}
				else
				{
					$map=$map."<li>".$tempTitle."</li>";
					$tempIndex=$pastIndex-$nowIndex;
					for($i=0;$i<$tempIndex;$i++)
					{
						$map=$map."</ul></li>";
					}
					$pastIndex=$nowIndex;
					$tempTitle=$tempArray[$m]['Title'];
				}
			}
			else
			{
				$headerFlag=True;
				$pastIndex=$nowIndex;
				$tempTitle=$tempArray[$m]['Title'];
			}
		}
		$map=$map."<li>".$tempTitle."</li>";
		$tempIndex=$pastIndex-$mainHeaderIndex;
		for($i=0;$i<$tempIndex;$i++)
		{
			$map=$map."</ul></li>";
		}
		$map=$map."</ul>";
		return $map;
	}
	function simpleText($html)
	{
		$html = str_replace('&ndash;','-',$html);
		$html = str_replace('&quot;','"',$html);
		$html = preg_replace('/\&amp;(nbsp);/','&${1};',$html);

		//formatting
		// bold
		$html = preg_replace('/\'\'\'([^\n\']+)\'\'\'/','<strong>${1}</strong>',$html);
		// emphasized
		$html = preg_replace('/\'\'([^\'\n]+)\'\'?/','<em>${1}</em>',$html);
		//interwiki links
		$html = preg_replace_callback('/\[\[([^\|\n\]:]+)[\|]([^\]]+)\]\]/','helper_interwikilinks',$html);
		// without text
		$html = preg_replace_callback('/\[\[([^\|\n\]:]+)\]\]/','helper_interwikilinks',$html);
		
		
		
		
		$html = preg_replace('/{{([^\|\n\}]+)([\|]?([^\}]+))+\}\}/','Interwiki: ${1} &raquo; ${3}',$html);
		// Template
		// categories
		$html = preg_replace('/\[\[([^\|\n\]]{2})([\:]([^\]]+))?\]\]/','Translation: ${1} &raquo; ${3}',$html);
		$html = preg_replace('/\[\[(file|img):((ht|f)tp(s?):\/\/(.+?))( (.+))*\]\]/i','<img src="$2" alt="$6"/>',$html);
		
		// image
		$html = preg_replace('/\[\[([^\|\n\]]+)([\|]([^\]]+))+\]\]/','Image: ${0}+${1}+${2}+${3}',$html);
		
		//links
		//Link without text
		
		$html = preg_replace('/\[((news|(ht|f)tp(s?)|irc):\/\/(.+?))( (.+))\]/i','<a href="$1">$7</a>',$html);
		$html = preg_replace('/\[((news|(ht|f)tp(s?)|irc):\/\/(.+?))\]/i','<a href="$1">$1</a>',$html);
		$html = preg_replace_callback('/\[([^\[\]\|\n\': ]+)\]/','helper_externlinks',$html);
		// with text
		$html = preg_replace_callback('/\[([^\[\]\|\n\' ]+)[\| ]([^\]\']+)\]/','helper_externlinks',$html);
		
		// allowed tags
		$html = preg_replace('/&lt;(\/?)(small|sup|sub|u)&gt;/','<${1}${2}>',$html);
		
		$html = preg_replace('/\n*&lt;br *\/?&gt;\n*/',"\n",$html);
		$html = preg_replace('/&lt;(\/?)(math|pre|code|nowiki)&gt;/','<${1}pre>',$html);
		$html = preg_replace('/&lt;!--/','<!--',$html);
		$html = preg_replace('/--&gt;/',' -->',$html);
		
		//Indentations
		$html = preg_replace('/[\n\r]: *.+([\n\r]:+.+)*/','<dl>$0</dl>',$html);
		$html = preg_replace('/^:(?!:) *(.+)$/m','<dd>$1</dd>',$html);
		$html = preg_replace('/([\n\r]:: *.+)+/','<dd><dl>$0</dl></dd>',$html);
		$html = preg_replace('/^:: *(.+)$/m','<dd>$1</dd>',$html);
		
		// headings
		for($i=7;$i>0;$i--)
		{
			$html = preg_replace('/\n+[=]{'.$i.'}([^=]+)[=]{'.$i.'}\n*/','<h'.$i.'>${1}</h'.$i.'>',$html);
		}
		
		//lists
		//Unorded List
		$html = preg_replace('/[\n\r]?\*.+([\n|\r]\*.+)+/','<ul>$0</ul>'."\n",$html);
		$html = preg_replace('/[\n\r]\*(?!\*) *(.+)(([\n\r]\*{2,}.+)+)/','<li>$1<ul>$2</ul></li>'."\n",$html);
		$html = preg_replace('/[\n\r]\*{2}(?!\*) *(.+)(([\n\r]\*{3,}.+)+)/','<li>$1<ul>$2</ul></li>'."\n",$html);
		$html = preg_replace('/[\n\r]\*{3}(?!\*) *(.+)(([\n\r]\*{4,}.+)+)/','<li>$1<ul>$2</ul></li>'."\n",$html);
		
		//Orded List
		$html = preg_replace('/[\n\r]?#.+([\n|\r]#.+)+/','<ol>$0</ol>'."\n",$html);
		$html = preg_replace('/[\n\r]#(?!#) *(.+)(([\n\r]#{2,}.+)+)/','<li>$1<ol>$2</ol></li>'."\n",$html);
		$html = preg_replace('/[\n\r]#{2}(?!#) *(.+)(([\n\r]#{3,}.+)+)/','<li>$1<ol>$2</ol></li>'."\n",$html);
		$html = preg_replace('/[\n\r]#{3}(?!#) *(.+)(([\n\r]#{4,}.+)+)/','<li>$1<ol>$2</ol></li>'.'</ol>'."\n",$html);
		
		
		// List items
		$html = preg_replace('/[ ]*[\*#]+([^\n]*)/','<li>${1}</li>',$html);
		$html = preg_replace('/----/','<hr />',$html);

		// line breaks
		$html = preg_replace('/[\n\r]{4}/',"<br/><br/>",$html);
		$html = preg_replace('/[\n\r]{2}/',"<br/>",$html);
		$html = preg_replace('/[>]<br\/>[<]/',"><",$html);
		$html = preg_replace('/^(?!<li|dd).+(?=(<a|strong|em|img)).+$/mi',"$0<br/>",$html);
		
		return $html;
	}
	function parse($text)
	{
		$html="\n\n".$text;
		$html = html_entity_decode($html);
		$html = str_replace('&ndash;','-',$html);
		$html = str_replace('&quot;','"',$html);
		$html = preg_replace('/\&amp;(nbsp);/','&${1};',$html);
		//$html = convertTables($html);
		$html = simpleText($html);
		return $html;
	}
	function parseRaw($title,$page)
	{
		putMilestone("ParseRaw start");
		$text = (getPartBetween($page, '<text xml:space="preserve">', '</text>'));
		$html = $text;
		//echo "<!-- " . wordwrap($text,120,"\n",1) . " -->";
		// re-html
		$html = html_entity_decode($html);
		$html = str_replace('&ndash;','-',$html);
		$html = str_replace('&quot;','"',$html);
		$html = preg_replace('/\&amp;(nbsp);/','&${1};',$html);
		$html = str_replace('{{PAGENAME}}',$title,$html);
		// Table
		//$html = convertTables($html);
		$html = simpleText($html);
		putMilestone("ParseRaw done");
		return $html;
	}
	/*function giveSource($page)
	{
		putMilestone("giveSource start");
		$text = (getPartBetween($page, '<text xml:space="preserve">', '</text>'));
		$text = "<pre>".$text."</pre>";
		putMilestone("giveSource done");
		return $text;
	}*/
	function helper_externlinks($matches)
	{
		$target = $matches[1];
		$text = empty($matches[2])?$matches[1]:$matches[2];
		return '<a href="wiki/'.$target.'">'.$text.'</a>';
	}
	function helper_interwikilinks($matches){
		$target = $matches[1];
		$text = empty($matches[2])?$matches[1]:$matches[2];
		$class=" class=\"dunno\" ";
		return '<a '.$class.' href="wiki/'.$target.'">'.$text.'</a>';
	}

?>
