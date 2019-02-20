#!/usr/bin/env python
#coding:utf-8

import logging
import logging.config
import os
import time
import json
import sys
import toml

reload(sys)
sys.setdefaultencoding('utf-8')
sys.dont_write_bytecode = True
os.chdir(os.path.dirname(os.path.abspath(__file__)))
os.environ['PATH'] = os.path.dirname(sys.executable) + os.pathsep + os.getenv('PATH')
# os.environ['DEBUG'] = '1'
config = toml.load(os.getenv('PYTHON_ENV', 'development') + '.toml')
logging.basicConfig(format='%(asctime)s [%(levelname)s] process@%(process)s thread@%(thread)s %(filename)s@%(lineno)s - %(funcName)s(): %(message)s', level=logging.INFO)


import pychrome


def pychrome_send_click(tab, x, y, button='left'):
    """https://github.com/cyrus-and/chrome-remote-interface/wiki/Trigger-synthetic-click-events"""
    assert isinstance(tab, pychrome.Tab)
    assert isinstance(x, int) and isinstance(y, int)
    # info = tab.Input.dispatchMouseEvent(type='mouseMoved', x=x, y=y, button='left', clickCount=1, modifiers=0)
    info = tab.Input.dispatchMouseEvent(type='mousePressed', x=x, y=y, button=button, clickCount=1, modifiers=0)
    info = tab.Input.dispatchMouseEvent(type='mouseReleased', x=x, y=y, button=button, clickCount=1, modifiers=0)
    return info


def pychrome_send_keys(tab, char):
    assert isinstance(tab, pychrome.Tab)
    assert isinstance(char, basestring) and len(char) == 1
    info = tab.Input.dispatchKeyEvent(type='rawKeyDown', windowsVirtualKeyCode=ord(char), unmodifiedText=char, text=char)
    info = tab.Input.dispatchKeyEvent(type='char', windowsVirtualKeyCode=ord(char), unmodifiedText=char, text=char)
    info = tab.Input.dispatchKeyEvent(type='keyUp', windowsVirtualKeyCode=ord(char), unmodifiedText=char, text=char)
    return info


def pychrome_call_element_js(tab, query, js):
    assert isinstance(tab, pychrome.Tab)
    assert isinstance(query, basestring) and isinstance(js, basestring)
    tab.DOM.enable()
    tab.DOM.getDocument()
    info = tab.DOM.performSearch(query=query, includeUserAgentShadowDOM=True)
    logging.info('pychrome_call: %r DOM.performSearch(%r) return %s', tab, query, info)
    info = tab.DOM.getSearchResults(searchId=info['searchId'], fromIndex=0, toIndex=info['resultCount'])
    info = tab.DOM.resolveNode(nodeId=info['nodeIds'][0])
    info = tab.Runtime.callFunctionOn(objectId=info['object']['objectId'], functionDeclaration=js)
    logging.info('pychrome_call: %r callFunctionOn(%r) return %s', tab, js[:128], info)
    return info


def pychrome_wait_element_appeared(tab, query, timeout, predicate=None):
    assert isinstance(tab, pychrome.Tab)
    assert isinstance(query, basestring) and isinstance(timeout, (int, float))
    while timeout > 0:
        try:
            if query.startswith('document.'):
                info = tab.Runtime.evaluate(expression=query)
                logging.info('pychrome_wait_element_appeared: tab.Runtime.evaluate(%s) return: %s', query, info)
                if callable(predicate) and not predicate(info['result']):
                    continue
            else:
                info = pychrome_call_element_js(tab, query, js_element_getter('outerHTML'))
                if callable(predicate) and not predicate(info['result']):
                    continue
            return True
        except (KeyError, pychrome.CallMethodException) as e:
            logging.info('pychrome_wait_element_appeared(%r, %r) error: %s(%s)', tab, query, type(e), e)
        finally:
            time.sleep(1.0)
            timeout -= 1.0
    return False


def pychrome_get_document_value(tab, expression):
    assert isinstance(tab, pychrome.Tab)
    assert isinstance(expression, basestring)
    tab.DOM.enable()
    tab.DOM.getDocument()
    info = tab.Runtime.evaluate(expression=expression)
    logging.info('pychrome_get_elements_html: %r tab.Runtime.evaluate(%r) return %s', tab, expression, info)
    value = info['result']['value']
    if expression.startswith('JSON.stringify('):
        value = json.loads(value)
    return value

def js_element_caller(call):
    return '(function() { this.%s() })' % call

def js_element_getter(name):
    return '(function() { return this.%s })' % name

def js_element_setter(name, value):
    return '(function() { this.%s= "%s" })' % (name, value)

def js_element_position():
    return '(function() {x=this.offsetLeft;y=this.offsetTop;i=this.offsetParent;while(i!==null){x+=i.offsetLeft;y+=i.offsetTop;i=i.offsetParent;}return x+" "+y;})'

def js_document_get_htmls(cssselector):
    return 'JSON.stringify(Array.prototype.slice.call(document.querySelectorAll("%s")).map(function(e){return e.outerHTML}))' % cssselector

def js_document_get_html(cssselector):
    return 'document.querySelector("%s").outerHTML' % cssselector

def js_document_get_tagattr(tag, attr):
    return 'document.getElementsByTagName("%s")[0].attributes["%s"].value' % (tag, attr)

def js_document_get_text(element_id):
    return 'document.getElementById("%s").innerText' % element_id


def main():
    browser = pychrome.Browser('http://127.0.0.1:9222')
    tab = browser.new_tab()
    tab.start()
    tab.Network.enable()
    tab.Page.enable()
    tab.Page.navigate(url='https://itunesconnect.apple.com/', _timeout=5)
    pychrome_wait_element_appeared(tab, '#footer', 3)
    if pychrome_get_document_value(tab, 'location.href').endswith('/login'):
        email = 'phuslu@hotmail.com'
        password = '123456'
        pychrome_wait_element_appeared(tab, '#iforgot-link', 10)
        pychrome_call_element_js(tab, '#appleId', js_element_setter('value', email))
        pychrome_call_element_js(tab, '#pwd', js_element_setter('value', password[:-1]))
        pychrome_call_element_js(tab, '#pwd', js_element_caller('focus'))
        pychrome_send_keys(tab, password[-1])
        pychrome_send_keys(tab, '\r')
        time.sleep(2)
        pychrome_wait_element_appeared(tab, '#footer', 10)
    tab.Page.navigate(url='https://reportingitc2.apple.com/insights.html?pageid=6', _timeout=10)
    for _ in xrange(400):
        svg_wait_predicate = lambda result: len(result.get('value', '')) > 10240
        svg_expression = 'document.querySelector("div.widget-grid-item.row-2").querySelector("svg").outerHTML'
        title_expression = 'document.querySelector("span.endDate").innerText'
        pychrome_wait_element_appeared(tab, svg_expression, 60, predicate=svg_wait_predicate)
        svg = pychrome_get_document_value(tab, svg_expression)
        title = pychrome_get_document_value(tab, title_expression)
        with open(u'svgs/%s.svg' % title.strip(), 'wb') as fp:
            fp.write(svg)
        pychrome_call_element_js(tab, '.date-prev', js_element_caller('click'))
        time.sleep(1)


if __name__ == "__main__":
    main()

