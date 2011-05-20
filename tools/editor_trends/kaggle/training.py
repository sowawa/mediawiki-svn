import codecs
import os



location = '/home/diederik/wikimedia/wikilytics/en/wiki/txt'
files = os.listdir(location)

output = codecs.open('training.txt', 'w', 'utf-8')

for filename in files:
    fh = codecs.open(os.path.join(location, filename))
    for line in fh:
        line = line.strip()
        line = line.split('\t')
        if len(line) != 13:
            continue
        username = line[12].lower()
        if username.endswith('bot'):
            line[5] = 1
        line = '\t'.join(line)
        output.write(line)
    
    
output.close()