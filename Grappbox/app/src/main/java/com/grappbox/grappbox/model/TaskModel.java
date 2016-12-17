package com.grappbox.grappbox.model;


import android.content.Context;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Parcel;
import android.os.Parcelable;
import android.util.Pair;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract.TaskDependenciesEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskEntry;

import java.text.ParseException;
import java.util.Date;
import java.util.List;

public class TaskModel implements Parcelable {
    public static final String[] projection = {
            TaskEntry.TABLE_NAME + "." + TaskEntry._ID,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_GRAPPBOX_ID,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_TITLE,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_DUE_DATE_UTC,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_START_DATE_UTC,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_FINISHED_DATE_UTC,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_CREATED_AT_UTC,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_IS_MILESTONE,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_IS_CONTAINER,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_PARENT_ID,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_ADVANCE,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_LOCAL_CREATOR,
            TaskEntry.TABLE_NAME + "." + TaskEntry.COLUMN_DESCRIPTION
    };

    public static final int _ID = 0, GRAPPBOX_ID = 1, TITLE = 2, DUE_DATE = 3, START_DATE = 4, FINISHED_DATE = 5, CREATED_AT = 6,
                            IS_MILESTONE = 7, IS_CONTAINER = 8, PARENT_ID = 9, ADVANCE = 10, CREATOR_ID = 11, DESCRIPTION = 12;

    public enum LinkType
    {
        NONE,
        START_TO_START,
        END_TO_START,
        START_TO_END,
        END_TO_END
    }

    public long _id;
    public String grappboxId;
    public String title;
    public String description;
    public String due_date;
    public String start_date;
    public String finished_date;
    public String createdAt_date;
    public boolean isMilestone;
    public boolean isContainer;
    public long parentId;
    public int advance;
    public long creatorId;
    public List<Pair<Long, LinkType>> links;


    protected TaskModel(Parcel in) {
        _id = in.readLong();
        grappboxId = in.readString();
        title = in.readString();
        description = in.readString();
        due_date = in.readString();
        start_date = in.readString();
        finished_date = in.readString();
        createdAt_date = in.readString();
        isMilestone = in.readByte() != 0;
        isContainer = in.readByte() != 0;
        parentId = in.readLong();
        advance = in.readInt();
        creatorId = in.readLong();
    }

    public static final Creator<TaskModel> CREATOR = new Creator<TaskModel>() {
        @Override
        public TaskModel createFromParcel(Parcel in) {
            return new TaskModel(in);
        }

        @Override
        public TaskModel[] newArray(int size) {
            return new TaskModel[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_id);
        dest.writeString(grappboxId);
        dest.writeString(title);
        dest.writeString(description);
        dest.writeString(due_date);
        dest.writeString(start_date);
        dest.writeString(finished_date);
        dest.writeString(createdAt_date);
        dest.writeByte((byte) (isMilestone ? 1 : 0));
        dest.writeByte((byte) (isContainer ? 1 : 0));
        dest.writeLong(parentId);
        dest.writeInt(advance);
        dest.writeLong(creatorId);
    }



    public TaskModel(Cursor data, Context context){
        _id = data.getLong(_ID);
        grappboxId = data.getString(GRAPPBOX_ID);
        title = data.getString(TITLE);
        description = data.getString(DESCRIPTION);
        due_date = data.getString(DUE_DATE);
        start_date = data.getString(START_DATE);
        finished_date = data.getString(FINISHED_DATE);
        createdAt_date = data.getString(CREATED_AT);
        isMilestone = data.getInt(IS_MILESTONE) > 0;
        isContainer = data.getInt(IS_CONTAINER) > 0;
        parentId = data.getLong(PARENT_ID);
        advance = data.getInt(ADVANCE);
        creatorId = data.getLong(CREATOR_ID);
        new DependenciesLoader().execute(context);
    }

    public Date getEndDate() throws ParseException {
        return Utils.Date.getDateFromUTCAPIToPhone(due_date);
    }

    public Date getStartDate() throws ParseException {
        return Utils.Date.getDateFromUTCAPIToPhone(start_date);
    }


    private class DependenciesLoader extends AsyncTask<Context, Void, Void>{

        @Override
        protected Void doInBackground(Context... params) {
            links.clear();
            Cursor dependencies = params[0].getContentResolver().query(TaskDependenciesEntry.CONTENT_URI, new String[]{
                TaskDependenciesEntry.COLUMN_TYPE,
                TaskDependenciesEntry.COLUMN_LOCAL_TASK_TO
            }, TaskDependenciesEntry.COLUMN_LOCAL_TASK_FROM+"=?", new String[]{String.valueOf(_id)}, null);
            if (dependencies == null || !dependencies.moveToFirst())
                return null;
            do{
                LinkType type;
                switch (dependencies.getString(1)){
                    case "fs":
                        type = LinkType.END_TO_START;
                        break;
                    case "sf":
                        type = LinkType.START_TO_END;
                        break;
                    case "ss":
                        type = LinkType.START_TO_START;
                        break;
                    case "ff":
                        type = LinkType.END_TO_END;
                        break;
                    default:
                        type = LinkType.START_TO_END;
                        break;
                }
                links.add(new Pair<>(dependencies.getLong(0), type));
            } while (dependencies.moveToNext());
            dependencies.close();
            return null;
        }
    }
}
