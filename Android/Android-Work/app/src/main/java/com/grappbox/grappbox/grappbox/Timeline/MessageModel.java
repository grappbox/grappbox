package com.grappbox.grappbox.grappbox.Timeline;

/**
 * Created by tan_f on 30/05/2016.
 */
public class MessageModel {

    private String _title;
    private String _desc;
    private String _date;
    private String _hour;
    private String _user;

    public void setTitle(String title){
        _title = title;
    }

    public void setDesc(String desc){
        _desc = desc;
    }

    public void setDate(String date){
        _date = date;
    }

    public void setHour(String hour){
        _hour = hour;
    }

    public void setUser(String user){
        _user = user;
    }

    public String getTitle(){
        return _title;
    }

    public String getDesc(){
        return _desc;
    }

    public String getDate(){
        return _date;
    }

    public String getHour(){
        return _hour;
    }

    public String getUser(){
        return _user;
    }
}
