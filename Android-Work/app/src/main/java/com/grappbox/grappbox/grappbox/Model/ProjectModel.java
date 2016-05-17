package com.grappbox.grappbox.grappbox.Model;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.media.Image;
import android.util.Base64;
import android.util.DisplayMetrics;
import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.Serializable;
import java.util.Map;

/**
 * Created by wieser_m on 24/01/2016.
 */
public class ProjectModel implements Serializable {

    private int id;
    private String name;
    private String description;
    private String phone;
    private String company;
    private byte[] logo;
    private String contact_mail;
    private String facebookURL;
    private String twitterURL;
    private String deletedAt;

    public ProjectModel()
    {
        id = -1;
    }

    public ProjectModel(JSONObject data) throws JSONException {
        byte[] blob = Base64.decode(data.getString("project_logo"), Base64.DEFAULT);

        id = data.getInt("project_id");
        name = data.getString("project_name");
        description = data.getString("project_description");
        phone = data.getString("project_phone");
        company = data.getString("project_company");
        logo = Base64.decode(data.getString("project_logo"), Base64.DEFAULT);
        contact_mail = data.getString("contact_mail");
        facebookURL = data.getString("facebook");
        twitterURL = data.getString("twitter");
        JSONObject date = data.isNull("delete_at") ? null : data.getJSONObject("deleted_at");
        if (date != null)
            Log.e("WATCH", date.toString());

        deletedAt = (date == null || date.isNull("date") ? null : date.getString("date"));
    }

    public boolean isValid()
    {
        return id > 0;
    }

    public int getId() {
        return id;
    }

    public boolean isDeleted() {
        return (deletedAt != null);
    }

    public void setDeletedAt(String date)
    {
        deletedAt = date;
    }
    public String getName() {
        return name;
    }

    public String getDescription() {
        return description;
    }

    public String getPhone() {
        return phone;
    }

    public String getCompany() {
        return company;
    }

    public Bitmap getLogo(Context context) {
        BitmapFactory.Options opt = new BitmapFactory.Options();

        DisplayMetrics metrics = context.getResources().getDisplayMetrics();
        opt.inScreenDensity = metrics.densityDpi;
        opt.inTargetDensity =  metrics.densityDpi;
        opt.inDensity = DisplayMetrics.DENSITY_DEFAULT;
        return BitmapFactory.decodeByteArray(logo, 0, logo.length, new BitmapFactory.Options());
    }

    public String getContact_mail() {
        return contact_mail;
    }

    public String getFacebookURL() {
        return facebookURL;
    }

    public String getTwitterURL() {
        return twitterURL;
    }

    public void setName(String name) {
        this.name = name;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }

    public void setCompany(String company) {
        this.company = company;
    }

    public void setLogo(String logo) {
        this.logo = Base64.decode(logo, Base64.DEFAULT);
    }

    public void setContact_mail(String contact_mail) {
        this.contact_mail = contact_mail;
    }

    public void setFacebookURL(String facebookURL) {
        this.facebookURL = facebookURL;
    }

    public void setTwitterURL(String twitterURL) {
        this.twitterURL = twitterURL;
    }
}
