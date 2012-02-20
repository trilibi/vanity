<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * <http://www.opensource.org/licenses/mit-license.php>
 */


use Vanity\Event\Dispatcher,
    Vanity\Event\InputOutput as EventIO;


/*%**********************************************************************%*/
// CONSOLE

Dispatcher::get()
	->addListener('console.fetch.checkout', function(EventIO $event)
	{
		$event = new Vanity\Console\FetchEvent($event->get_output());
		return $event->checkout();
	});

Dispatcher::get()
	->addListener('console.fetch.update', function(EventIO $event)
	{
		$event = new Vanity\Console\FetchEvent($event->get_output());
		return $event->update();
	});


/*%**********************************************************************%*/
// CONFIG

Dispatcher::get()
	->addListener('config.read', function(EventIO $event)
	{
		$event = new Vanity\Config\ConfigEvent($event->get_input(), $event->get_output());
		return $event->read();
	});

Dispatcher::get()
	->addListener('config.display', function(EventIO $event)
	{
		$event = new Vanity\Config\ConfigEvent($event->get_input(), $event->get_output());
		return $event->display();
	});


/*%**********************************************************************%*/
// LEXER

Dispatcher::get()
	->addListener('parser.lexer.find_project_files', function(EventIO $event)
	{
		$event = new Vanity\Parse\ParseEvent($event->get_input(), $event->get_output());
		return $event->find_project_files();
	});
