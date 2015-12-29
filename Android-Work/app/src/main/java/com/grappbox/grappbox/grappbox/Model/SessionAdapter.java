package com.grappbox.grappbox.grappbox.Model;

/**
 * Created by Arkanice on 02/12/2015.
 */
public class SessionAdapter {

    private static SessionAdapter _instance = null;

    private String _fisrname = null;
    private String _lastname = null;
    private String _userToken = null;
    private float _userID;
    private boolean _isLogged = false;

    private SessionAdapter()
    {

    }

    public static SessionAdapter getInstance()
    {
        if (_instance == null){
            _instance = new SessionAdapter();
        }
        return _instance;
    }

    public String getFisrname()
    {
        return _fisrname;
    }

    public String getLastname()
    {
        return _lastname;
    }

    public boolean isLogged()
    {
        return _isLogged;
    }

    public String getToken()
    {
        return _userToken;
    }

    public void LogInUser(float id, String firstname, String lastname, String token)
    {
        _userID = id;
        _fisrname = firstname;
        _lastname = lastname;
        _userToken = token;
        _isLogged = true;
    }
}
