package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

import java.util.Objects;

/**
 * Created by marc on 08/10/2016.
 */

public class UserModel implements Parcelable {

    private boolean mAvatarLoaded;
    public long _id;
    public String mFirstname;
    public String mLastname;
    public String mBirthday;
    public String mAvatarURI;
    public String mEmail;

    public UserModel(long _id, String mFirstname, String mLastname, String mBirthday, String mAvatarURI, String email) {
        this._id = _id;
        this.mFirstname = mFirstname;
        this.mLastname = mLastname;
        this.mBirthday = mBirthday;
        this.mAvatarURI = mAvatarURI;
        this.mEmail = email;
        mAvatarLoaded = false;
    }

    public UserModel(Cursor data){
        mAvatarLoaded = false;
        _id = data.getLong(data.getColumnIndex(UserEntry._ID));
        mFirstname = data.getString(data.getColumnIndex(UserEntry.COLUMN_FIRSTNAME));
        mLastname = data.getString(data.getColumnIndex(UserEntry.COLUMN_LASTNAME));
        mBirthday = data.getString(data.getColumnIndex(UserEntry.COLUMN_DATE_BIRTHDAY_UTC));
        mAvatarURI = data.getString(data.getColumnIndex(UserEntry.COLUMN_URI_AVATAR));
        mEmail = data.getString(data.getColumnIndex(UserEntry.COLUMN_CONTACT_EMAIL));
    }

    protected UserModel(Parcel in) {
        mAvatarLoaded = in.readByte() != 0;
        _id = in.readLong();
        mFirstname = in.readString();
        mLastname = in.readString();
        mBirthday = in.readString();
        mAvatarURI = in.readString();
        mEmail = in.readString();
    }

    public boolean isAvatarLoaded(){
        return mAvatarLoaded;
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

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeByte((byte) (mAvatarLoaded ? 1 : 0));
        dest.writeLong(_id);
        dest.writeString(mFirstname);
        dest.writeString(mLastname);
        dest.writeString(mBirthday);
        dest.writeString(mAvatarURI);
        dest.writeString(mEmail);
    }

    public static String[] toStringArray(UserModel[] arr){
        String[] ret = new String[arr.length];
        for (int i = 0; i < arr.length; ++i){
            ret[i] = arr[i].mFirstname + " " + arr[i].mLastname;
        }
        return ret;
    }

    @Override
    public boolean equals(Object obj) {
        return obj instanceof UserModel && ((UserModel) obj)._id == this._id;
    }
}
