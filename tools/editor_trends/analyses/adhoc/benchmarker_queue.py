from Queue import Queue
#import cProfile
from guppy import hpy
h = hpy()

q1, q2, q3 = Queue(), Queue(), Queue()
h.heap()
print 'ughh'
for x in xrange(1000):
    q1.put(x)
    q2.put({})
    q3.put([])
    #h = hpy()
hpy().doc
h.heap()
#    for x in xrange(100):
#        a = q1.get()
#        b = q2.get()
#        c = q3.get()
#    h.heap()

#if __name__ == '__main__':
#    main()
    #cProfile.run('main()')
