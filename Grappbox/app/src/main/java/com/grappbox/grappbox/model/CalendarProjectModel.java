package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract;

/**
 * Created by tan_f on 06/11/2016.
 */

public class CalendarProjectModel implements Parcelable {

    public long _localProjectId;
    public String _grappboxId;
    public String _projectName;


    public CalendarProjectModel() {
        _localProjectId = -1;
        _grappboxId = "-1";
        _projectName = "No Project";
    }

    public CalendarProjectModel(Cursor cursor) {
        _localProjectId = cursor.getLong(cursor.getColumnIndex(GrappboxContract.ProjectEntry._ID));
        _grappboxId = cursor.getString(cursor.getColumnIndex(GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID));
        _projectName = cursor.getString(cursor.getColumnIndex(GrappboxContract.ProjectEntry.COLUMN_NAME));
    }

    public CalendarProjectModel(Parcel source) {
        _localProjectId = source.readLong();
        _grappboxId = source.readString();
        _projectName = source.readString();
    }

    public long getLocalProjectId(){ return _localProjectId; }

    @Override
    public int describeContents() { return 0; }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_localProjectId);
        dest.writeString(_grappboxId);
        dest.writeString(_projectName);
    }

    public static String[] toStringArray(CalendarProjectModel[] array){
        String[] ret = new String[array.length];
        for (int i = 0; i < array.length; ++i) {
            ret[i] = array[i]._projectName;
        }
        return ret;
    }

    public static final Parcelable.Creator<CalendarProjectModel> CREATOR = new Parcelable.Creator<CalendarProjectModel>() {
        @Override
        public CalendarProjectModel createFromParcel(Parcel source) {
            return new CalendarProjectModel(source);
        }

        @Override
        public CalendarProjectModel[] newArray(int size) {
            return new CalendarProjectModel[size];
        }
    };

}
