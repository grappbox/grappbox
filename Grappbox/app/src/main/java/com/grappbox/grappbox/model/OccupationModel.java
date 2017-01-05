package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract;

/**
 * Created by Arka on 05/01/2017.
 */

public class OccupationModel implements Parcelable {

    public int  _id;
    public long _isBusy;
    public long _taskBegun;
    public long _taskOnGoing;
    public String   _userLastName;
    public String   _userFirstName;

    public OccupationModel(Cursor cursor) {
        super();
        _id = cursor.getInt(cursor.getColumnIndex(GrappboxContract.OccupationEntry._ID));
        _isBusy = cursor.getLong(cursor.getColumnIndex(GrappboxContract.OccupationEntry.COLUMN_IS_BUSY));
        _taskBegun = cursor.getLong(cursor.getColumnIndex(GrappboxContract.OccupationEntry.COLUMN_COUNT_TASK_BEGUN));
        _taskOnGoing = cursor.getLong(cursor.getColumnIndex(GrappboxContract.OccupationEntry.COLUMN_COUNT_TASK_ONGOING));
        _userFirstName = cursor.getString(cursor.getColumnIndex(GrappboxContract.UserEntry.COLUMN_FIRSTNAME));
        _userLastName = cursor.getString(cursor.getColumnIndex(GrappboxContract.UserEntry.COLUMN_LASTNAME));
    }

    public OccupationModel(Parcel source) {
        _id = source.readInt();
        _isBusy = source.readLong();
        _taskBegun = source.readLong();
        _taskOnGoing = source.readLong();
        _userFirstName = source.readString();
        _userLastName = source.readString();
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(_id);
        dest.writeLong(_isBusy);
        dest.writeLong(_taskBegun);
        dest.writeLong(_taskOnGoing);
        dest.writeString(_userFirstName);
        dest.writeString(_userLastName);
    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final Parcelable.Creator<OccupationModel> CREATOR = new Parcelable.Creator<OccupationModel>() {

        @Override
        public OccupationModel createFromParcel(Parcel source) {
            return new OccupationModel(source);
        }

        @Override
        public OccupationModel[] newArray(int size) {
            return new OccupationModel[size];
        }
    };
}
