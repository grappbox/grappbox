package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.icu.util.DateInterval;
import android.icu.util.TimeZone;
import android.os.Parcel;
import android.os.Parcelable;
import android.support.annotation.Nullable;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;

/**
 * Created by marcw on 31/08/2016.
 */
public class UserModel implements Parcelable {
    public final static SimpleDateFormat birthdayFormatter = new SimpleDateFormat("yyyy-MM-dd");
    public final static SimpleDateFormat grappboxFormatter = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");
    public String firstname, lastname, email, phone, country, linkedin, twitter;
    public String password;
    public Date birthday;
    //TODO : put avatar model
    public Date last_edited_avatar_utc;

    public UserModel(String firstname, String lastname, String email, String phone, String country, String linkedin, String twitter, String password, Date birthday) {
        this.firstname = firstname;
        this.lastname = lastname;
        this.email = email;
        this.phone = phone;
        this.country = country;
        this.linkedin = linkedin;
        this.twitter = twitter;
        this.password = password;
        this.birthday = birthday == null ? new Date() : birthday;
    }

    public UserModel(Cursor cursor) {
        if (cursor.moveToFirst())
        {
            firstname = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_FIRSTNAME));
            lastname = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_LASTNAME));
            email = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_CONTACT_EMAIL));
            phone = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_CONTACT_PHONE));
            country = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_COUNTRY));
            linkedin = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_SOCIAL_LINKEDIN));
            twitter = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_SOCIAL_TWITTER));
            password = cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_PASSWORD));
            birthday = new Date(cursor.getLong(cursor.getColumnIndex(UserEntry.COLUMN_DATE_BIRTHDAY_UTC)));
            last_edited_avatar_utc  = new Date(cursor.getLong(cursor.getColumnIndex(UserEntry.COLUMN_DATE_AVATAR_LAST_EDITED_UTC)));
        }
    }

    public UserModel (JSONObject json, @Nullable Cursor cursor) throws JSONException, ParseException {
        JSONObject data = json.getJSONObject("data");
        String date;

        if (data == null)
            throw new JSONException("Invalid JSON : data object doesn't exist");
        firstname = data.getString("firstname");
        lastname = data.getString("lastname");
        email = data.getString("lastname");
        phone = data.getString("phone");
        country = data.getString("country");
        linkedin = data.getString("linkedin");
        twitter = data.getString("twitter");
        date = data.getString("birthday");
        birthday = birthdayFormatter.parse(date);

        JSONObject avatarDate = data.getJSONObject("avatar");
        last_edited_avatar_utc = Utils.Date.getDateFromGrappboxAPIToUTC(avatarDate.getString("date"));

        if (cursor != null && cursor.moveToFirst()) {
            cursor.getString(cursor.getColumnIndex(UserEntry.COLUMN_PASSWORD));
        }
    }

    protected UserModel(Parcel in){
        firstname = in.readString();
        lastname = in.readString();
        email = in.readString();
        phone = in.readString();
        country = in.readString();
        linkedin = in.readString();
        twitter = in.readString();
        password = in.readString();
        birthday = new Date(in.readLong());
        last_edited_avatar_utc = new Date(in.readLong());
    }

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(firstname == null ? "" : firstname);
        dest.writeString(lastname == null ? "" : lastname);
        dest.writeString(email == null ? "" : email);
        dest.writeString(phone == null ? "" : country);
        dest.writeString(country == null ? "" : country);
        dest.writeString(linkedin == null ? "" : linkedin);
        dest.writeString(twitter == null ? "" : twitter);
        dest.writeString(password == null ? "" : password);
        dest.writeLong(birthday.getTime());
        dest.writeLong(last_edited_avatar_utc.getTime());
    }

    public static final Creator<UserModel> CREATOR = new Creator<UserModel>() {
        @Override
        public UserModel createFromParcel(Parcel in) {
            return new UserModel(in);
        }

        @Override
        public UserModel[] newArray(int size) {
            return new UserModel[size];
        }
    };
}
