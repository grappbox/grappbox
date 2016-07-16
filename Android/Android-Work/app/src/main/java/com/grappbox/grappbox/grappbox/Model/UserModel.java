package com.grappbox.grappbox.grappbox.Model;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 27/01/2016.
 */
public class UserModel {

    private int id;
    private String firstname;
    private String lastname;

    public UserModel()
    {
        id = -1;
    }

    public UserModel(JSONObject json) throws JSONException {
        id = json.getInt("id");
        firstname = json.getString("firstname");
        lastname = json.getString("lastname");
    }

    public boolean isValid()
    {
        return id > 0;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getFirstname() {
        return firstname;
    }

    public void setFirstname(String firstname) {
        this.firstname = firstname;
    }

    public String getLastname() {
        return lastname;
    }

    public void setLastname(String lastname) {
        this.lastname = lastname;
    }

    public String getCompleteName()
    {
        return firstname + " " + lastname;
    }
}
