package com.grappbox.grappbox.grappbox.Model;

import android.content.ContentValues;
import android.os.Debug;
import android.util.Log;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.ProtocolException;
import java.net.URL;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.Vector;

/**
 * Created by Arkanice on 08/12/2015.
 */
public class APIConnectAdapter  {

    private static APIConnectAdapter _instance = null;

    private static final String _APIUrlBase = "http://api.grappbox.com/app_dev.php/";
    private static final String _APIBaseVersion = "V0.11";

    String _APIVersion = _APIBaseVersion;
    HttpURLConnection _connection = null;
    BufferedReader reader = null;
    String resultAPI;
    URL _url;
    DataOutputStream _dataOutputStream;
    String _TypeRequest = "GET";

    private APIConnectAdapter() {

    }

    public void setVersion(String version)
    {
        _APIVersion = version;
    }

    public static APIConnectAdapter getInstance()
    {
        if (_instance == null){
            _instance = new APIConnectAdapter();
        }
        return _instance;
    }

    public void startConnection(String url) throws IOException
    {
        String connectURL = _APIUrlBase + _APIVersion + "/" + url;
        Log.v("URL", connectURL);
        _url = new URL(connectURL);
        _connection = (HttpURLConnection) _url.openConnection();
        _connection.setReadTimeout(10000);
        _connection.setConnectTimeout(15000);
    }

    public void setRequestConnection(String typeRequest) throws ProtocolException
    {
        _TypeRequest = typeRequest;
        _connection.setRequestMethod(_TypeRequest);
    }

    public void sendJSON(JSONObject JSONParam) throws IOException
    {
        _dataOutputStream = new DataOutputStream(_connection.getOutputStream());
        _dataOutputStream.write(JSONParam.toString().getBytes("UTF-8"));
        _dataOutputStream.flush();
        _dataOutputStream.close();
        _connection.connect();
    }

    public int getResponseCode() throws IOException
    {
        int status = _connection.getResponseCode();
        Log.v("status:", String.valueOf(status));
        return status;
    }

    public String getInputSream() throws IOException
    {

        InputStream inputStream = _connection.getInputStream();
        StringBuffer buffer = new StringBuffer();
        if (inputStream == null) {
            return null;
        }
        reader = new BufferedReader(new InputStreamReader(inputStream));

        String line;
        String nLine;
        while ((line = reader.readLine()) != null) {
            nLine = line + "\n";
            buffer.append(nLine);
        }

        if (buffer.length() == 0) {
            return null;
        }

        resultAPI = buffer.toString();
        return resultAPI;
    }

    public void closeConnection()
    {
        if (_connection != null){
            _connection.disconnect();
            _APIVersion = _APIBaseVersion;
        }
        if (reader != null){
            try {
                reader.close();
            } catch (final IOException e){
                Log.e("APIConnection", "Error ", e);
            }
        }
    }

    public ContentValues getLoginData(String resultJSON) throws JSONException
    {
        final String DATA_LIST = "user";
        final String[] DATA_USER = {"id", "firstname", "lastname", "email", "token"};

        if (resultJSON.length() == 0 || resultJSON.length() == 3)
            return null;
        ContentValues JSONContent = new ContentValues();
        JSONObject jsonObject = new JSONObject(resultJSON);
        JSONObject userData = jsonObject.getJSONObject(DATA_LIST);

        for (String data : DATA_USER) {
            if (userData.getString(data) == null)
                return null;
            JSONContent.put(data, userData.getString(data));
        }

        return JSONContent;
    }

    public List<ContentValues> getListTeamOccupation(String result) throws JSONException
    {
        final String[] DATA_TEAM = {"project_name", "user_id", "first_name", "last_name", "occupation", "number_of_tasks_begun", "number_of_ongoing_tasks"};

        if (result.length() == 0 || result.length() == 3)
            return null;
        JSONObject forecastJSON = new JSONObject(result);
        List<ContentValues> list = new Vector<ContentValues>();
        int i = 0;
        while (1 == 1) {
            String person = "Person " + String.valueOf(i);
            if (!forecastJSON.has(person) || forecastJSON.getString(person).length() == 0)
                break;
            ContentValues values = new ContentValues();
            for (int data = 0; data < DATA_TEAM.length; ++data){
                values.put(DATA_TEAM[data], forecastJSON.getJSONObject(person).getString(DATA_TEAM[data]));
            }
            list.add(values);
            ++i;
        }
        return list;
    }

    public List<ContentValues> getListGlobalProgress(String result) throws JSONException
    {
        final String[] DATA_PROGRESS = {
                "project_id",
                "project_name",
                "project_description",
                "project_phone",
                "project_company",
                "project_logo",
                "contact_mail",
                "facebook",
                "twitter",
                "number_finished_tasks",
                "number_ongoing_tasks",
                "number_tasks",
                "number_bugs",
                "number_messages"};

        if (result.length() == 0 || result.length() == 3)
            return null;
        JSONObject forecastJSON = new JSONObject(result);
        List<ContentValues> list = new Vector<ContentValues>();
        int i = 0;
        while (1 == 1) {
            String project = "Project " + String.valueOf(i);
            if (!forecastJSON.has(project) || forecastJSON.getString(project).length() == 0)
                break;
            ContentValues values = new ContentValues();
            for (int data = 0; data < DATA_PROGRESS.length; ++data){
                if (forecastJSON.getJSONObject(project).getString(DATA_PROGRESS[data]) == null)
                    values.put(DATA_PROGRESS[data], "");
                else
                    values.put(DATA_PROGRESS[data], forecastJSON.getJSONObject(project).getString(DATA_PROGRESS[data]));
            }
            list.add(values);
            ++i;
        }
        return list;
    }

    public List<ContentValues> getListNextMeeting(String result)  throws JSONException
    {
        final String[] DATA_MEETING = {"project_name", "project_logo", "event_type", "event_title", "event_description", "event_begin_date", "event_end_date"};
        final String[] DATA_DATE_EVENT = {"date", "timezone"};
        final String[] KEY_MEETING = {"project_name", "project_logo", "event_type", "event_title", "event_description", "event_begin_date", "event_begin_place", "event_end_date", "event_end_place"};

        if (result.length() == 0 || result.length() == 3)
            return null;
        JSONObject forecastJSON = new JSONObject(result);
        List<ContentValues> list = new Vector<ContentValues>();
        int i = 0;
        while (1 == 1) {
            String person = "Meeting " + String.valueOf(i);
            if (!forecastJSON.has(person) || forecastJSON.getString(person).length() == 0)
                break;
            ContentValues values = new ContentValues();
            for (int data = 0; data < 5; ++data) {
                if (forecastJSON.getJSONObject(person).getString(DATA_MEETING[data]) == null)
                    values.put(KEY_MEETING[data], "");
                else
                    values.put(KEY_MEETING[data], forecastJSON.getJSONObject(person).getString(DATA_MEETING[data]));
            }
            values.put(KEY_MEETING[5], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[5]).getString(DATA_DATE_EVENT[0]));
            values.put(KEY_MEETING[6], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[5]).getString(DATA_DATE_EVENT[1]));
            values.put(KEY_MEETING[7], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[6]).getString(DATA_DATE_EVENT[0]));
            values.put(KEY_MEETING[8], forecastJSON.getJSONObject(person).getJSONObject(DATA_MEETING[6]).getString(DATA_DATE_EVENT[1]));

            list.add(values);
            ++i;
        }
        return list;
    }

    public ContentValues getUserInformation(String result)  throws JSONException {
        final String[] userInfo = {"first_name", "last_name", "birthday", "avatar", "email", "phone", "country", "linkedin", "viadeo", "twitter"};

        JSONObject forecastJSON = new JSONObject(result);
        Log.v("JSON", forecastJSON.toString());
        ContentValues values = new ContentValues();
        for (int data = 0; data < userInfo.length; ++data) {
            if (forecastJSON.getString(userInfo[data]) == null)
                values.put(userInfo[data], "");
            else
                values.put(userInfo[data], forecastJSON.getString(userInfo[data]));
        }
        Log.v("JSON", forecastJSON.getString(userInfo[2]));
        return values;
    }

    public void printContentValues(ContentValues values)
    {
        Set<Map.Entry<String, Object>> s= values.valueSet();
        Iterator itr = s.iterator();

        Log.d("DatabaseSync", "ContentValue Length :: " + values.size());

        while(itr.hasNext())
        {
            Map.Entry me = (Map.Entry)itr.next();
            String key = me.getKey().toString();
            Object value =  me.getValue();

            Log.d("DatabaseSync", "Key:"+key+", values:"+(String)(value == null?null:value.toString()));
        }
    }
}
