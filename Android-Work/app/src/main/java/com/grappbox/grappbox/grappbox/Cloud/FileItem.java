package com.grappbox.grappbox.grappbox.Cloud;

import android.util.ArrayMap;
import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Collections;
import java.util.Map;
import java.util.Objects;

/**
 * Created by wieser_m on 17/01/2016.
 */
public class FileItem {

    public enum EFileType{
        BACK,
        FILE,
        DIR
    }

    public enum EJsonKeys{
        SECURED,
        FILENAME,
        FILETYPE,
        MIMETYPE,
        FILESIZE,
        TIMESTAMP
    }

    private static final Map<EFileType, String> stringType;
    private static final Map<EJsonKeys, String> stringJsonKeys;

    static{
        Map<EFileType, String> types = new ArrayMap<>();
        Map<EJsonKeys, String> keys = new ArrayMap<>();

        types.put(EFileType.FILE, "file");
        types.put(EFileType.DIR, "dir");
        types.put(EFileType.BACK, "back");
        stringType = Collections.unmodifiableMap(types);

        keys.put(EJsonKeys.FILENAME, "filename");
        keys.put(EJsonKeys.SECURED, "isSecured");
        keys.put(EJsonKeys.FILETYPE, "type");
        keys.put(EJsonKeys.MIMETYPE, "mimetype");
        keys.put(EJsonKeys.FILESIZE, "size");
        keys.put(EJsonKeys.TIMESTAMP, "timestamp");
        stringJsonKeys = Collections.unmodifiableMap(keys);
    }

    private int             _size;
    private String          _mimetype;
    private int             _timestamp;
    private EFileType       _type;
    private String          _filename;
    private boolean         _isSecured;

    public FileItem() { }
    public FileItem(EFileType type, String filename){
        _type = type;
        _filename = filename;
    }

    public JSONObject toJson() throws JSONException {
        JSONObject obj = new JSONObject();

        obj.put(stringJsonKeys.get(EJsonKeys.FILETYPE), stringType.get(_type));
        obj.put(stringJsonKeys.get(EJsonKeys.FILENAME), _filename);
        obj.put(stringJsonKeys.get(EJsonKeys.SECURED), _isSecured);
        if (_type == EFileType.FILE)
        {
            obj.put(stringJsonKeys.get(EJsonKeys.FILESIZE), _size);
            obj.put(stringJsonKeys.get(EJsonKeys.MIMETYPE), _mimetype);
            obj.put(stringJsonKeys.get(EJsonKeys.TIMESTAMP), _timestamp);
        }
        return obj;
    }

    public void fromJson(JSONObject item)
    {
        try {
            _type = (Objects.equals(item.getString(stringJsonKeys.get(EJsonKeys.FILETYPE)), stringType.get(EFileType.DIR)) ? EFileType.DIR : EFileType.FILE);
            _filename = item.getString(stringJsonKeys.get(EJsonKeys.FILENAME));
            _isSecured = item.getBoolean(stringJsonKeys.get(EJsonKeys.SECURED));
            if (_type == EFileType.FILE)
            {
                _mimetype = item.getString(stringJsonKeys.get(EJsonKeys.MIMETYPE));
                _size = item.getInt(stringJsonKeys.get(EJsonKeys.FILESIZE));
                _timestamp = item.getInt(stringJsonKeys.get(EJsonKeys.TIMESTAMP));
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    public String toString()
    {
        try {
            JSONObject obj = toJson();
            return obj.toString();
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return "";
    }

    /* Generated Getters and Setters */
    public int get_size() {
        return _size;
    }

    public void set_size(int _size) {
        this._size = _size;
    }

    public String get_mimetype() {
        return _mimetype;
    }

    public void set_mimetype(String _mimetype) {
        this._mimetype = _mimetype;
    }

    public int get_timestamp() {
        return _timestamp;
    }

    public void set_timestamp(int _timestamp) {
        this._timestamp = _timestamp;
    }

    public EFileType get_type() {
        return _type;
    }

    public void set_type(EFileType _type) {
        this._type = _type;
    }

    public String get_filename() {
        return _filename;
    }

    public void set_filename(String _filename) {
        this._filename = _filename;
    }

    public boolean isSecured() {
        return _isSecured;
    }

    public void set_isSecured(boolean _isSecured) {
        this._isSecured = _isSecured;
    }
}
