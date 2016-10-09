package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;
import android.support.v4.content.ContextCompat;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;

/**
 * Created by marc on 09/10/2016.
 */

public class BugTagModel implements Parcelable {
    public String _id;
    public String name;
    public String color;

    public BugTagModel(String id, String name, String color){
        _id = id;
        this.name = name;
        this.color = color;
    }

    public BugTagModel(Cursor data){
        _id = data.getString(data.getColumnIndex(GrappboxContract.TagEntry._ID));
        name = data.getString(data.getColumnIndex(GrappboxContract.TagEntry.COLUMN_NAME));
        color = "#9E58DC";
    }

    protected BugTagModel(Parcel in) {
        _id = in.readString();
        name = in.readString();
        color = in.readString();
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(_id);
        dest.writeString(name);
        dest.writeString(color);
    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final Creator<BugTagModel> CREATOR = new Creator<BugTagModel>() {
        @Override
        public BugTagModel createFromParcel(Parcel in) {
            return new BugTagModel(in);
        }

        @Override
        public BugTagModel[] newArray(int size) {
            return new BugTagModel[size];
        }
    };
}
