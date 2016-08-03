# Web-Search-Engine
The project aims at scraping **USC Keck School of Medicine** using Crawler4j library with the constraints like max-depth set to 5 and maximum number of pages to crawl set to 5000. The Search Engine incorporates the following three phases of Information Retrieval:
* Scraping
* Indexing
* Ranking

#Pre-requisites:
* Crawler4j installed [https://github.com/yasserg/crawler4j]
* UNIX environment setup if working on windows using Virtual Box
* Solr latest version installed
* Python 3.0 installed
* NetworkX installation[https://networkx.github.io/]
* Tika parser latest version installed
* Porter Stemmer and Peter-Norvig Spell Corrector

#Procedure
* Use crawler4j to provide the seed URL and override the functions namely 
    * shouldVisit
    * handlePageStatusCode
    * visit
  in order to download HTML,DOC and PDF files from the website
* After the pages have been downloaded and the other DOC and PDF files are sent to Solr for indexing
* The pagerankdata.csv file which is constructed during the downloading of HTML,DOC and PDF files is fed into the NetworkX library in Python to implement PageRank algorithm
* Using PHP, jQuery and HTML the user interface is fashioned so as to give the top 10 relevant documents for the Query searched by the user based on the option of default Solr which uses Solr to fetch the documents or on the option of PageRank algorithm which uses NetworkX graph in Python to give the results
* Then using Tika Parser extract the data from the downloaded HTMl,DOC and PDF file and store it in big.txt which is then used by the thrid party API: **Peter-Norvig** spell corrector
* Along with the Peter-Norvig spell corrector , Porter Stemmer is also incorporated for stemming before the big.txt file can be used for the Spell corrector and the Auto suggest functionality

#Evaluation
Evaluation of the Search Engine is done on the basis of the relevant results produced by the Search Engine. For more information please refer to the report4.pdf
