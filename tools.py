# -*- coding: UTF-8 -*-

# by default try to use monotonic timer witch is not subjected to local machine time changes
try:
    from time import monotonic as timer
except ImportError:
    from time import time as timer

from distutils.version import LooseVersion



class StopWatch:

    runtime = None
    stop = None
    start = None

    def __enter__(self):
        self.start = timer()
        return self

    def __exit__(self, exc_type, exc_value, traceback):
        self.stop = timer()
        self.calc()
        return False

    def calc(self):
        self.runtime = round( (self.stop - self.start), 2 )


def vcmp(v1, v2, op):
    '''
    Compare v1 and v2 using op.

    op:
        operator.op object
    '''

    v1 = str(v1)
    v2 = str(v2)

    return op(LooseVersion(v1), LooseVersion(v2))
