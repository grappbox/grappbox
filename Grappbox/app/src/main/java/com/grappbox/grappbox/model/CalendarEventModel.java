package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;

/**
 * Created by tan_f on 26/10/2016.
 */

public class CalendarEventModel implements Parcelable {

    public long        _id;
    public long        _projectId;
    public long        _localCreatorId;
    public String      _title;
    public String      _description;
    public String      _beginDate;
    public String      _endDate;
    public List<UserModel> _user;
    public long          _userCount;

    public CalendarEventModel(Cursor cursor){
        _id = cursor.getLong(cursor.getColumnIndex(GrappboxContract.EventEntry._ID));
        _projectId = cursor.getLong(cursor.getColumnIndex(GrappboxContract.EventEntry.COLUMN_LOCAL_PROJECT_ID));
        _localCreatorId = cursor.getLong(cursor.getColumnIndex(GrappboxContract.EventEntry.COLUMN_LOCAL_CREATOR_ID));
        _title = cursor.getString(cursor.getColumnIndex(GrappboxContract.EventEntry.COLUMN_EVENT_TITLE));
        _description = cursor.getString(cursor.getColumnIndex(GrappboxContract.EventEntry.COLUMN_EVENT_DESCRIPTION));
        _beginDate = cursor.getString(cursor.getColumnIndex(GrappboxContract.EventEntry.COLUMN_DATE_BEGIN_UTC));
        _endDate = cursor.getString(cursor.getColumnIndex(GrappboxContract.EventEntry.COLUMN_DATE_END_UTC));
        _userCount = 0;
    }

    public CalendarEventModel(Parcel source){
        _id = source.readLong();
        _projectId = source.readLong();
        _localCreatorId = source.readLong();
        _title = source.readString();
        _description = source.readString();
        _beginDate = source.readString();
        _endDate = source.readString();
        Parcelable[] arrParticipant = source.readParcelableArray(UserModel.class.getClassLoader());
        _user = new ArrayList<>();
        for (Parcelable part : arrParticipant){
            _user.add((UserModel) part);
        }
        _userCount = source.readLong();
    }

    public void setParticipant(List<UserModel> participant) {
        _user = participant;
        _userCount = _user == null ? 0 : _user.size();
    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final Parcelable.Creator<CalendarEventModel> CREATOR = new Parcelable.Creator<CalendarEventModel>() {
        @Override
        public CalendarEventModel createFromParcel(Parcel source) {
            return new CalendarEventModel(source);
        }

        @Override
        public CalendarEventModel[] newArray(int size) {
            return new CalendarEventModel[size];
        }
    };

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_id);
        dest.writeLong(_projectId);
        dest.writeLong(_localCreatorId);
        dest.writeString(_title);
        dest.writeString(_description);
        dest.writeString(_beginDate);
        dest.writeString(_endDate);
        dest.writeParcelableArray(_user.toArray(new UserModel[_user.size()]), 0);
        dest.writeLong(_userCount);
    }
}
