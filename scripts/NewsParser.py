#coding=utf-8
import MySQLdb
import sys
import codecs
import json
import os
import os.path
from pyjsparser import PyJsParser

rootdir = "C:\\news\\xinhuanet"

def parseOneXinhuaNetJSFile(path, pub_id, titleList):
	file = open(path, "r")
	#file = codecs.open("C:\\news\\xinhuanet\\4002\\235\\33540.js", "r", encoding="utf-8")

	decodedStr = file.readlines()

	if len(decodedStr) == 0:
		print "Read Empty File"
		return

	if len(decodedStr[0]) <= 22 or len(decodedStr[0]) >= (1024 * 512):
		print "File not valid"
		return

	text = unicode(decodedStr[0], 'utf-8') 

	p = PyJsParser()
	result = p.parse(text)

	#print result

	valueDict = dict()

	if result.get("body"):
		for member in result["body"]:
			if member.get("declarations"):
				for declaration in member["declarations"]:
					#print declaration["init"];
					if declaration.get("init"):
						init = declaration["init"]
						if init.get("properties"):
							for property in init["properties"]:
								#print ("key: %s, value: %s\n" %(str(property["key"]).encode("gbk"), str(property["value"]).encode("gbk")))
								#test = json.loads(str(property["value"]).encode("gbk"))

								try:
									key = property["key"]["value"]
									value = json.dumps(property["value"]["value"], ensure_ascii=False)
									''' , ensure_ascii=False '''
									#print key
									#print value
									valueDict[key] = value
									#dict[str(property["key"]["value"]).encode("gbk")] = str(property["value"]["value"].encode("gbk"));
									#print ("key: %s, value: %s\n" %(str(property["key"]).encode("gbk"), str(property["value"]).encode("gbk")))
									pass
								except Exception as e:
									pass
								else:
									pass
								finally:
									pass

	if len(valueDict) < 3:
		return

	topic = (valueDict["topic"].encode("utf-8")[1:-1]).replace("\\\"", "\"")
	if valueDict.get("content"):
		content = (valueDict["content"].encode("utf-8")[1:-1])
	elif valueDict.get("summary"):
		content = (valueDict["summary"].encode("utf-8")[1:-1])

	shareurl = (valueDict["shareurl"].encode("utf-8")[1:-1])
	releasedate = "20" + (valueDict["releasedate"].encode("utf-8")[1:-1])

	found = False
	for title in titleList:
		if title == topic:
			found = True
			break

	sql = ""
	if found != True:
		try:
			#print dict;
			print topic
			#print valueDict["content"]
			print releasedate
			#print valueDict["shareurl"]
			sql = "insert into news values(NULL,'" + topic + "','" + "" + "','" + content + "','" + shareurl + "','" + releasedate + "','" + str(pub_id) + "','" + path.replace("\\", "\\\\") + "')"
			cur.execute(sql)
		except Exception as e:
			print "sql execute failed"
		else:
			pass
		finally:
			#conn.commit()
			pass
	else:
		print "pass"

	#print result

conn= MySQLdb.connect(
	host='localhost',
	port = 3306,
	user='root',
	passwd='1q2w3e',
	db ='trustednews',
	)
cur = conn.cursor()

cur.execute("select publishers.id from publishers where publishers.name = \"新华网\"")

results = cur.fetchall()

for row in results:
	for i in range(0, len(row)):
		pub_id = row[i]

cur.execute("SELECT news.title FROM news")

results = cur.fetchall()
titleList = list()

for row in results:
	for i in range(0, len(row)):
		titleList.append(row[i])

for parent,dirnames,filenames in os.walk(rootdir):		#三个参数：分别返回1.父目录 2.所有文件夹名字（不含路径） 3.所有文件名字
	for dirname in  dirnames:							#输出文件夹信息
		print "parent is: " + parent
		print "dirname is: " + dirname

	for filename in filenames:							#输出文件信息
		print "parent is: " + parent
		print "filename is: " + filename
		path = os.path.join(parent,filename)
		print "the full name of the file is: " + path	#输出文件路径信息
		parseOneXinhuaNetJSFile(path, pub_id, titleList)

cur.close()
conn.commit()
conn.close()