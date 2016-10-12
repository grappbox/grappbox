package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;

import java.text.DateFormat;
import java.text.ParseException;
import java.util.Locale;

/**
 * Created by marc on 08/10/2016.
 */

public class BugCommentModel implements Parcelable {
    public long _id;
    public String mDescription;
    public String mDate;
    public UserModel mAuthor;

    public BugCommentModel(Cursor data){
        _id = data.getLong(data.getColumnIndex(GrappboxContract.BugEntry._ID));
        mDescription = data.getString(data.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DESCRIPTION));
        try {
            mDate = DateFormat.getDateInstance(DateFormat.SHORT, Locale.getDefault()).format(Utils.Date.convertUTCToPhone(data.getString(data.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC))));
        } catch (ParseException e) {
            e.printStackTrace();
        }
        mAuthor = new UserModel("", data.getString(data.getColumnIndex(GrappboxContract.UserEntry.COLUMN_FIRSTNAME)),data.getString(data.getColumnIndex(GrappboxContract.UserEntry.COLUMN_LASTNAME)), "", data.getString(data.getColumnIndex(GrappboxContract.UserEntry.COLUMN_URI_AVATAR)), data.getString(data.getColumnIndex(GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL)));
    }

    protected BugCommentModel(Parcel in) {
        _id = in.readLong();
        mDescription = in.readString();
        mDate = in.readString();
        mAuthor = in.readParcelable(UserModel.class.getClassLoader());
    }

    public static final Creator<BugCommentModel> CREATOR = new Creator<BugCommentModel>() {
        @Override
        public BugCommentModel createFromParcel(Parcel in) {
            return new BugCommentModel(in);
        }

        @Override
        public BugCommentModel[] newArray(int size) {
            return new BugCommentModel[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_id);
        dest.writeString(mDescription);
        dest.writeString(mDate);
        dest.writeParcelable(mAuthor, flags);
    }
}
