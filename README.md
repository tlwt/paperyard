# PAPERYARD

Paperyard is the tool for autonomously naming and archiving scanned documents based on rules. It covers the entire workflow. From text recognition to filing.

Individual rules are used to recognize information such as sender, recipient or subject. Unlike other document management systems, Paperyard stores the data in the file name:
    
    date - sender - subject (recipient) (price) [tags] -- original_filename.pdf
    
No essential data is stored in an obscure database. Simple viewing, simple backup. The documents remain yours.

## Installation

The easiest way is to execute via docker. Make sure docker and git are installed on your machine. Launch the terminal of your choice and run the following lines:

    git clone https://github.com/tlwt/paperyard.git
    cd paperyard
    ./docker-build.sh
    ./docker-run.sh
    
Paperyard can be cloned to any directory. See [Configuration](#configuration) to learn how to personalise Paperyard.

## Usage

Paperyard lives from an individual set of rules. Follow this basic introduction to learn how to create your first few rules.

For [this example](https://paperyard.ams3.digitaloceanspaces.com/paperyard_sample.pdf), we create a rule set that recognizes the sender, recipient and subject.
- Navigate your browser to [localhost](http://localhost) (or wherever you are running the container).
- First we create a rule that recognizes our sender "Acme DemoTec". Navigate to _Rules -> Senders_. _Needles_ are comma-separated, case insensitive search terms. If these are detected in the document, the sender specified under _Company_ is assigned to this document. Set _Score_ to "100" and leave "Tags" blank. Click on _add_.
- To recognize the invoice as such, we now go to _Rules -> Subjects_. The idea of _Needles_ is also applied here. In addition, we can use _Company_ to restrict the application of the rule to a previously identified sender. We set _Needles_ to "Invoice" and _Company_ to "Acme DemoTec". In case of a hit, we want to receive "Invoice" as the subject. We again set _Score_ to "100" and leave _Tags_ blank. Click on _add_.
- In order to be able to assign the invoice more easily, we also want to recognize the recipient. To do this, under _Rules -> Recipient_ enter "Test Business" in the _Long Name_ field. _Long Name_ behaves like _Needles_. Enter "Business" as the value for _Name For File_. Click on _add_.

## Configuration

Paperyard can be adapted to your own wishes and requirements. Almost all important settings are made in [`/config/paperyard`](config/paperyard). Explanations and examples are given for each setting.

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request.

## Credits

Jannik Kramer ([@jannik-kramer](https://github.com/jannik-kramer))  
Till Witt ([@tlwt](https://github.com/tlwt))

## License

The MIT License (MIT)

Copyright 2017 consider it GmbH

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
