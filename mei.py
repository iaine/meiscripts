'''
   Conversion to simple MEI
'''

import urllib2
import json

def _write(fhandle, tag):
    '''
       Write a tag
    '''
    fhandle.write("<"+tag+">")

header = """meiHead>
  <fileDesc>
    <titleStmt>
      <title>MEI example</title>
    </titleStmt>
    <pubStmt>
      <respStmt>
        <corpName authURI="http://www.oerc.ox.ac.uk">Oxford e-Research Centre</corpName>
      </respStmt>
    </pubStmt>
  </fileDesc>
</meiHead"""

with open("mei.xml",'wb') as f:
    _write(f, 'mei meiversion="2.1.1" xml:id="http://www.music-encoding.org/ns/mei"')
    _write(f, header)
    _write(f, 'music')
    _write(f,'body')
    _write(f, 'mdiv')
    _write(f, 'score')
    _write(f, 'section')
    _write(f, 'measure')
    _write(f, 'staff')
    _write(f, 'layer')
    
    #with open("out.txt", "rb") as fl:
        #data = fl.readlines()
        #for da in data:
            #d = da.split()
            #_write(f, 'note xml:id= "' + str(d[1]).replace('.','') + '" pname="'+d[0]+'" dur="1" /')
    #fl.closed
    data = json.load(urllib2.urlopen('http://127.0.0.1:5984/sonify/_design/mei/_view/MEItransform'))
    for da in data["rows"]:
        _write(f, 'note xml:id= "' + da["id"] + '" pnum="'+str(da["value"])+'" dur="1" /')
  
    _write(f, '/layer')
    _write(f, '/staff')
    _write(f, '/measure')
    _write(f, '/section')
    _write(f, '/score')
    _write(f, '/mdiv')
    _write(f, '/body')
    _write(f, "/music")
    _write(f, "/mei")
f.closed
