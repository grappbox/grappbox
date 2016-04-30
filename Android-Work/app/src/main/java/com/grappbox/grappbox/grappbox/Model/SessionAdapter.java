package com.grappbox.grappbox.grappbox.Model;

import android.content.Context;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;

import java.util.HashMap;

/**
 * Created by Arkanice on 02/12/2015.
 */
public class SessionAdapter {

    private static SessionAdapter _instance = null;

    private SharedPreferences _pref;
    private Editor _editor;
    private Context _context;

    private int PRIVATE_MODE = 0;
    private static final String PREF_NAME = "GrappboxSessionPref";

    public static final String KEY_FIRST_NAME = "first_name";
    public static final String KEY_LAST_NAME = "last_name";
    public static final String KEY_TOKEN = "token";
    public static final String KEY_USERID = "userID";
    public static final String KEY_LOGIN = "login";
    public static final String KEY_PASSWORD = "password";
    public static final String KEY_ISLOGGED = "idLogged";

    private int    _currentSelectedProject = 1;

    private SessionAdapter(Context context)
    {
        _context = context;
        _pref = _context.getSharedPreferences(PREF_NAME, PRIVATE_MODE);
        
        _editor = _pref.edit();
    }

    public static synchronized void initializeInstance(Context context) {
        if (_instance == null) {
            _instance = new SessionAdapter(context);
        }
    }

    public static synchronized SessionAdapter getInstance()
    {
        if (_instance == null){
            throw new IllegalStateException(SessionAdapter.class.getSimpleName() +
                    " is not initialized, call initializeInstance(..) method first.");
        }
        return _instance;
    }

    public String getUserData(String KEY)
    {
        return _pref.getString(KEY, null);
    }

    public boolean isLogged()
    {
        return _pref.getBoolean(KEY_ISLOGGED, false);
    }

    public String getToken()
    {
        return _pref.getString(KEY_TOKEN, null);
    }

    public String getUserID()
    {
        return _pref.getString(KEY_USERID, null);
    }

    public String getPassword()
    {
        return _pref.getString(KEY_PASSWORD, null);
    }

    public String getLogin()
    {
        return _pref.getString(KEY_LOGIN, null);
    }

    public int getCurrentSelectedProject() { return _currentSelectedProject; }

    public void LogInUser(String id, String firstname, String lastname, String token, String login, String password)
    {
        _editor.putString(KEY_USERID, id);
        _editor.putString(KEY_FIRST_NAME, firstname);
        _editor.putString(KEY_LAST_NAME, lastname);
        _editor.putString(KEY_TOKEN, token);
        _editor.putString(KEY_LOGIN, login);
        _editor.putString(KEY_PASSWORD, password);
        _editor.putBoolean(KEY_ISLOGGED, true);
        _editor.commit();
    }

    public HashMap<String, String> getUserInformations()
    {
        HashMap<String, String> user = new HashMap<String, String>();

        user.put(KEY_USERID, _pref.getString(KEY_USERID, null));
        user.put(KEY_FIRST_NAME, _pref.getString(KEY_FIRST_NAME, null));
        user.put(KEY_LAST_NAME, _pref.getString(KEY_LAST_NAME, null));
        user.put(KEY_TOKEN, _pref.getString(KEY_TOKEN, null));
        user.put(KEY_LOGIN, _pref.getString(KEY_LOGIN, null));
        user.put(KEY_PASSWORD, _pref.getString(KEY_PASSWORD, null));

        return user;
    }

    public boolean LogoutUser()
    {
        return _editor.clear().commit();
    }

    public void setCurrentSelectedProject(int projectId)
    {
        _currentSelectedProject = projectId;
    }
}
