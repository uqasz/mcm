#-*- coding: UTF-8 -*-

from socket import error as SOCKET_ERROR, timeout as SOCKET_TIMEOUT
from mock import MagicMock, patch
import unittest
import pytest

from mcm.librouteros.exceptions import ConnError, LoginError, CmdError
from mcm.librouteros import connect, _encode_password



def test_password_encoding():
    assert _encode_password( '259e0bc05acd6f46926dc2f809ed1bba', 'test') == '00c7fd865183a43a772dde231f6d0bff13'



@patch('mcm.librouteros.Api')
@patch('mcm.librouteros.ReaderWriter')
@patch('mcm.librouteros.create_connection')
@patch('mcm.librouteros._encode_password')
class Test_connect(unittest.TestCase):


    def test_raises_ConnError_if_SOCKET_ERROR(self,  encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        create_conn_mock.side_effect = SOCKET_ERROR('some error')
        self.assertRaises(ConnError, connect, 'host', 'user', 'password')

    def test_raises_ConnError_if_SOCKET_TIMEOUT(self,  encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        create_conn_mock.side_effect = SOCKET_TIMEOUT('some error')
        self.assertRaises(ConnError, connect, 'host', 'user', 'password')

    def test_create_connection_with_default_argsuments(self,  encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        connect('host', 'user', 'password')
        create_conn_mock.assert_called_once_with(('host', 8728), 10, ('', 0))

    def test_instantiates_ReaderWriter(self,  encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        connect('host','user','pw', logger='logger')
        rwo_mock.assert_called_once_with( create_conn_mock.return_value, 'logger' )

    def test_calls_api_run_with_login(self, encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        connect('host', 'user', 'pass')
        api_mock.return_value.run.assert_any_call( '/login' )

    def test_calls_api_run_with_username_and_password(self, encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        encode_password_mock.return_value = 'pass'
        connect('host', 'user', 'pass')
        api_mock.return_value.run.assert_any_call( '/login', {'name':'user', 'response':'pass'} )

    def test_raises_LoginError_when_login_fails_with_CmdError(self, encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        api_mock.return_value.run.side_effect = CmdError
        self.assertRaises( LoginError, connect, 'host', 'user', 'pass' )

    def test_after_CmdError_calls_rwo_close(self, encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        api_mock.return_value.run.side_effect = CmdError
        with self.assertRaises( LoginError ):
            connect('host', 'user', 'pw')

        rwo_mock.return_value.close.assert_called_once_with()

    def test_returns_api_object(self, encode_password_mock, create_conn_mock, rwo_mock, api_mock):
        returning = connect('host', 'user', 'pw')
        self.assertEqual( api_mock.return_value, returning )


