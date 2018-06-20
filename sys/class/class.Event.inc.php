<?php

declare(strict_types=1);

class Event
{

    public $id;
    public $title;
    public $description;
    public $start;
    public $end;

    public function __construct($event=NULL)
    {
        if ( is_array($event) )
        {
            $this->id = $event['event_id'];
            $this->title = $event['event_title'];
            $this->description = $event['event_desc'];
            $this->start = $event['event_start'];
            $this->end = $event['event_end'];
        }
        else
        {
            $this->id = NULL;
            $this->title = "";
            $this->description = "";
            $this->start = "";
            $this->end = "";
        }
    }

}

?>