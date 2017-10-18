# Q-Branch: Web Services for every occasion
## Presented at CUC17
## Wednesday, October 25 - 11:15 AM

Q delivered to James Bond tools that were perfect for the situation Bond would be in. Using Cascade Web Services and Wing Ming Chan's PHP library, becoming Q for your website and content managers can be easy. From building WYSIWYG content blocks from plain text, to building page and and adding content blocks, and even automatically creating and updating faculty profiles, small tools built on Web Services can make laborious tasks easy. 

"Remember, if it hadn't been for Q Branch, you'd have been dead long ago." 

Specifically: 

- How CNU converted plain text to HTML WYSIWYG blocks 
- How faculty data is pulled and parsed from Digital Measures, added to blocks, put on pages and published automatically 
- How to convert spreadsheets of data into pages of content - and more!

The PHP files in this repository are dependent upon both [Parsedown](https://github.com/erusev/parsedown) for [Markdown](https://daringfireball.net/projects/markdown/) conversion, and Wing Ming Chan's [reusable code library for Cascade web services](https://github.com/wingmingchan/php7-cascade-ws-ns). The dependencies have been added to this repo as submodules.

- *blockMaker*: Converts markdown to named WYSIWYG blocks
- *learnMaker*: Converts CSV of link data to data definition block
- *facultyMaker*: Converts JSON from Digital Measures to faculty data definition blocks, creates pages, attaches data definition block and adds faculty photo.