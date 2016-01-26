package com.grappbox.grappbox.grappbox.Model;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 26/01/2016.
 */
public class CustomerAccessModel {
    public static final String GRAPPBOX_CUSTOMER_URL_BASE = "http://www.grappbox.com/autologin/";

    private int id;
    private String customerToken;
    private String name;


    public CustomerAccessModel(){ id = -1; }
    public CustomerAccessModel(JSONObject obj) throws JSONException {
        id = obj.getInt("id");
        customerToken = obj.getString("customer_token");
        name = obj.getString("name");
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public boolean isValid()
    {
        return id > 0;
    }

    public String getCustomerLoginUrl()
    {
        return GRAPPBOX_CUSTOMER_URL_BASE + customerToken;
    }

    public String getCustomerToken() {
        return customerToken;
    }

    public String getName() {
        return name;
    }

    public void setCustomerToken(String customerToken) {
        this.customerToken = customerToken;
    }

    public void setName(String name) {
        this.name = name;
    }
}
