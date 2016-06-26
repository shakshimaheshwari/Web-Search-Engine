import sys
import os
import tika
tika.initVM()
from tika import parser
def read_html_file(fileop):
    parsed = parser.from_file(fileop)
    return(parsed["content"])

def read_training_file(filename):
    filewr = open("big.txt","a+")
    HTMLDirs = next(os.walk(filename))[2]
    print(HTMLDirs)
    count=0
    for files in HTMLDirs:
        fileop = os.path.join(filename,files)
        text = read_html_file(fileop)
        print(text)
        filewr.write(text.encode('utf-8'))
        count = count+1
        print(count)

trainingfile = sys.argv[1]
read_training_file(trainingfile)     
