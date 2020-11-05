#!/usr/bin/env python
# -*- coding:utf-8 -*-

import re
import sys
import json

def extractFromPOFile(poPath):
  with open(poPath, 'r') as f:
    tuples = re.findall(r'msgid "(.+)"\nmsgstr "(.+)"', f.read())
  return tuples

def PO2JSON(filename):
  obj = {}
  print filename
  tuples = extractFromPOFile(filename)
  for tuple in tuples:
    obj[tuple[0].decode('utf-8')] = tuple[1].decode('utf-8')

  return json.dumps(obj)


arguments = sys.argv[1:]
xml_file_param = None
if len(arguments) >= 1:
  print(PO2JSON(sys.path[0] + '\\' + arguments[0]))
