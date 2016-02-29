package com.grappbox.grappbox.grappbox.Model;

import android.content.SharedPreferences;

/**
 * Created by Arkanice on 02/12/2015.
 */
public class SessionAdapter {

    private static SessionAdapter _instance = null;


    private String _fisrname = null;
    private String _lastname = null;
    private String _userToken = null;
    private String _userID;
    private String _login;
    private String _password;
    private boolean _isLogged = false;
    private int    _currentSelectedProject = 1;

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

    public String getUserID()
    {
        return _userID;
    }

    public String getPassword()
    {
        return _password;
    }

    public String getLogin()
    {
        return _login;
    }

    public int getCurrentSelectedProject() { return _currentSelectedProject; }

    public void LogInUser(String id, String firstname, String lastname, String token, String login, String password)
    {
        _userID = id;
        _fisrname = firstname;
        _lastname = lastname;
        _userToken = token;
        _isLogged = true;
        _login = login;
        _password = password;
    }

    public void setCurrentSelectedProject(int projectId)
    {
        _currentSelectedProject = projectId;
    }
}
