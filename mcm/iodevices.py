# -*- coding: UTF-8 -*-

from mcm.librouteros import TrapError, MultiTrapError
from mcm.exceptions import ReadError, WriteError


class StaticConfig:

    def __init__(self, data):
        self.data = data

    def read(self, path):
        for section in self.data:
            if section['path'] == path:
                return section['rules']

    def close(self):
        pass


class ReadOnlyRouterOS:

    actions = dict(ADD='add', SET='set', DEL='remove', GET='getall')

    def __init__(self, api):
        self.api = api

    def read(self, path):
        cmd = self.api.joinPath(path, self.actions['GET'])
        try:
            data = self.api(cmd=cmd)
        except (TrapError, MultiTrapError) as error:
            raise ReadError(error)
        return self.filter_dynamic(data)

    def write(self, path, action, data):
        pass

    def close(self):
        self.api.close()

    @staticmethod
    def filter_dynamic(data):
        return tuple(row for row in data if not row.get('dynamic'))


class RouterOsAPIDevice(ReadOnlyRouterOS):

    def write(self, path, action, data):
        command = self.api.joinPath(path, self.actions[action])
        method = getattr(self, action)
        method(command=command, data=data)

    def DEL(self, command, data):
        try:
            self.api(cmd=command, **{'.id': data['.id']})
        except (TrapError, MultiTrapError) as error:
            raise WriteError(error)

    def SET(self, command, data):
        try:
            self.api(cmd=command, **data)
        except (TrapError, MultiTrapError) as error:
            raise WriteError(error)

    def ADD(self, command, data):
        try:
            self.api(cmd=command, **data)
        except (TrapError, MultiTrapError) as error:
            raise WriteError(error)
