# List of Built-In Events

## Event Dispatcher

In order to register events with the _Event Dispatcher_, you need to get a reference to the Event Dispatcher singleton. You can do that by connecting to `Vanity\Event\Dispatcher::get()` (which is an instance of [`Symfony\Component\EventDispatcher\EventDispatcher`](https://github.com/symfony/EventDispatcher/blob/master/EventDispatcher.php)).

    use Vanity\Event\Dispatcher,
        Vanity\Event\InputOutput as EventIO;

    Dispatcher::get()
        ->addListener('event.name', function(EventIO $event)
        {
            $event = new MyClassThatExtendsFromEvent($event->get_input(), $event->get_output());
            return $event->event_name();
        });


## Console Events
### fetch
* `console.fetch.checkout`
* `console.fetch.update`

### parse

### generate
