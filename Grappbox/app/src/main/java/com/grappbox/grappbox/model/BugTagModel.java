package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract;

/**
 * Created by marc on 09/10/2016.
 */

public class BugTagModel implements Parcelable {
    public long _id;
    public String name;
    public String color;

    public BugTagModel(long id, String name, String color){
        _id = id;
        this.name = name;
        this.color = color;
    }

    public BugTagModel(Cursor data){
        _id = data.getLong(data.getColumnIndex(GrappboxContract.BugtrackerTagEntry._ID));
        name = data.getString(data.getColumnIndex(GrappboxContract.BugtrackerTagEntry.COLUMN_NAME));
        color = "#9E58DC";
    }

    protected BugTagModel(Parcel in) {
        _id = in.readLong();
        name = in.readString();
        color = in.readString();
    }

    public static String[] toStringArray(BugTagModel[] arr){
        String[] ret = new String[arr.length];

        for (int i = 0; i < arr.length; ++i){
            ret[i] = arr[i].name;
        }
        return ret;
    }

    @Override
    public boolean equals(Object obj) {
        return obj instanceof BugTagModel && ((BugTagModel) obj)._id == this._id;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_id);
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
