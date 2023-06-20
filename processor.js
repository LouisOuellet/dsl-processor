// DSL (Domain Specific Language) Processor
class Processor {
    #parsers;
    #items;
    #dsl;
  
    constructor() {
      this.clear();
    }
  
    addParser(type, handler) {
      this.#parsers[type] = { handler };
    }
  
    parse(dsl) {
        this.#dsl = dsl;
    
        // Break the DSL into sections by "end" keyword
        const sections = dsl.split('end').map(section => section.trim()).filter(Boolean);
        for (let section of sections) {
            // First line in each section is the type
            const lines = section.split('\n').map(line => line.trim()).filter(Boolean);
            const type = lines[0];
            if (!this.#parsers[type]) {
                throw new Error(`Unknown parser type "${type}"`);
            }
            // Parse each option as "name: 'value'" and add to options object
            const options = {};
            for (let i = 1; i < lines.length; i++) {
                const line = lines[i];
                const match = line.match(/(.*):\s*'(.*)'/);
                if (!match) {
                    throw new Error(`Malformed option line "${line}"`);
                }
                options[match[1]] = match[2];
            }
            // Push the parsed item to the list
            this.#items.push({ type, options });
        }
    
        return this; // Return this instance to allow for method chaining
    }
  
    process() {
        while (this.#items.length > 0) {
            const item = this.#items.shift();
            try {
                if (this.#parsers[item.type] && typeof this.#parsers[item.type].handler === 'function') {
                    this.#parsers[item.type].handler(item.options);
                }
            } catch (err) {
                console.error(`Error processing ${item.type}:`, err);
            }
        }
    
        return this; // Return this instance to allow for method chaining
    }
  
    clear() {
        this.#dsl = '';
        this.#items = [];
        this.#parsers = {};
    
        return this; // Return this instance to allow for method chaining
    }
}
const processor = new Processor();
