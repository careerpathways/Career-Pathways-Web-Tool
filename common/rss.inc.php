<?php


class RSSfeed {

	private $items;

	public $title;
	public $description;
	public $link;
	public $copyright;
	public $pubDate;

	function addItem($item) {
		$this->items[] = $item;
	}

	function Output() {
		header("Content-type: text/xml");

		$s = "";
		$s .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
		$s .= '<rss version="2.0">'."\n";

		$s .= '<channel>'."\n";

		$s .= '<title>'.$this->title.'</title>'."\n";
		$s .= '<description>'.$this->description."</description>\n";
		$s .= '<link>'.$this->link.'</link>'."\n";
		if( $this->copyright ) $s .= '<copyright>'.$this->copyright.'</copyright>'."\n";
		if( $this->pubDate ) {
			// if a timestamp is specified, automatically outputs a valid date format
			if( intval($this->pubDate) === $this->pubDate ) {
				$pubDate = date("D, d M Y H:i:s T",$this->pubDate);
			} else {
				$pubDate = $this->pubDate;
			}
			$s .= '<pubDate>'.$pubDate.'</pubDate>'."\n";
		}

		foreach( $this->items as $i ) {
			$s .= $i->toString();
		}

		$s .= '</channel>';
		$s .= '</rss>';

		echo $s;
	}



}

class RSSitem {

	public $title;
	public $description;
	public $link;
	public $guid;
	public $pubDate;  // unix timestamp format

	function toString() {
		$this->title = str_replace('&','&#38;',$this->title);

		$s = '<item>'."\n";
			$s .= "\t".'<title>'.$this->title.'</title>'."\n";
			if( $this->link ) $s .= "\t".'<link>'.$this->link.'</link>'."\n";
			if( $this->guid ) $s .= "\t".'<guid>'.$this->guid.'</guid>'."\n";
			$s .= "\t".'<description><![CDATA['.($this->description).']]></description>'."\n";

			// if a timestamp is specified, automatically outputs a valid date format
			if( intval($this->pubDate) === $this->pubDate ) {
				$pubDate = date("D, d M Y H:i:s T",$this->pubDate);
			} else {
				$pubDate = $this->pubDate;
			}

			$s .= "\t<pubDate>".$pubDate.'</pubDate>'."\n";
		$s .= '</item>'."\n";
		return $s;
	}

}


?>