<?php

class UtilityProcessor {
    private $parsers = [];
    private $items = [];
    private $dsl = '';

    public function addParser($type, callable $handler) {
        $this->parsers[$type] = $handler;
    }

    public function parse($dsl) {
        $this->dsl = $dsl;

        // Break the DSL into sections by "end" keyword
        $sections = array_filter(array_map('trim', explode('end', $dsl)));
        foreach ($sections as $section) {
            // First line in each section is the type
            $lines = array_filter(array_map('trim', explode("\n", $section)));
            $type = array_shift($lines);
            if (!isset($this->parsers[$type])) {
                throw new Exception("Unknown parser type '$type'");
            }
            // Parse each option as "name: 'value'" and add to options array
            $options = [];
            foreach ($lines as $line) {
                if (preg_match("/(.*):\s*'(.*)'/", $line, $matches) === 1) {
                    $options[$matches[1]] = $matches[2];
                } else {
                    throw new Exception("Malformed option line '$line'");
                }
            }
            // Push the parsed item to the list
            $this->items[] = ['type' => $type, 'options' => $options];
        }

        return $this;
    }

    public function process() {
        foreach ($this->items as $item) {
            try {
                $type = $item['type'];
                $options = $item['options'];
                if (isset($this->parsers[$type])) {
                    $handler = $this->parsers[$type];
                    $handler($options);
                }
            } catch (Exception $e) {
                echo "Error processing $type: ", $e->getMessage(), "\n";
            }
        }
        $this->items = [];  // Empty the items array after processing
    }

    public function clear() {
        $this->parsers = [];
        $this->items = [];
        $this->dsl = '';
    }
}

$processor = new UtilityProcessor();

// Add parsers
$processor->addParser('notification', function($options){ var_dump('Creating notification', $options); });
$processor->addParser('task', function($options){ var_dump('Creating task', $options); });

// Use the processor
$dsl = "
    notification
    label: 'New message!'
    color: 'blue'
    icon: 'bell'
    end
    task
    color: 'red'
    scale: '1.5'
    label: 'Important task'
    end
";

$processor->parse($dsl)->process();

?>

