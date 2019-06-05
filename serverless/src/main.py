import json
import MySQLdb

from flask import (
    Flask,
    render_template,
    jsonify
)


class Response:
  def __init__(self):
    self.name = 'Prueba'


data = { "nombre" : "Marcelo",
         "edad" : 5 }

app = Flask(__name__, template_folder="templates")
 

db = MySQLdb.connect("127.0.0.1","workflow","workflow123","engine" )

def main(input):
   try:
     print "Algo", repr(data)
     print data['nombre']

   except:
   	 print "Error"

@app.route('/')
def home():
	return render_template('home.html')

@app.route('/test')
def test():
  r = Response()
  res = make_response(jsonify(r.__dict__))
  res.headers['arkho-header'] = 'test'
  return res

if __name__ == '__main__':
	app.run(host='0.0.0.0',debug=True)

#main("algo")
