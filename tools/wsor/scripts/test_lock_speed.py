import time
from threading import Lock

num = 0
start = time.time()
for i in range(0, 1000):
	num = sum(n for n in range(0, i))

print("Without lock took %s seconds" % (time.time()-start))

num = 0
l = Lock()
start = time.time()
for i in range(0, 1000):
	l.acquire()
	num = sum(n for n in range(0, i))
	l.release()

print("With lock took %s seconds" % (time.time()-start))
