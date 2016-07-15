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
import java.util.concurrent.TimeoutException;

/**
 * Created by Arkanice on 08/12/2015.
 */
public class APIConnectAdapter  {

    private static APIConnectAdapter _instance = null;

    public static final String _APIUrlBase = "http://beta.api.grappbox.com/app_dev.php/";
    public static final String _APIBaseVersion = "V0.2";

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

    public static APIConnectAdapter getInstance(boolean... unique)
    {
        if (unique.length > 0 && unique[0])
            return new APIConnectAdapter();
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
        _connection.setReadTimeout(40000);
        _connection.setConnectTimeout(45000);
    }

    public void startConnection(String url, String version) throws IOException
    {
        String connectURL = _APIUrlBase + version + "/" + url;
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
        final String DATA_LIST = "data";
        final String[] DATA_USER = {"id", "firstname", "lastname", "email", "token"};

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

        JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
        JSONArray arrayJSON = forecastJSON.getJSONArray("array");
        List<ContentValues> list = new Vector<ContentValues>();
        for (int i = 0; i < arrayJSON.length(); ++i)
        {
            JSONObject obj = arrayJSON.getJSONObject(i);
            ContentValues values = new ContentValues();
            for (int data = 0; data < DATA_PROGRESS.length; ++data){
                if (obj.getString(DATA_PROGRESS[data]) == null)
                    values.put(DATA_PROGRESS[data], "");
                else
                    values.put(DATA_PROGRESS[data], obj.getString(DATA_PROGRESS[data]));
            }
            list.add(values);
        }
        return list;
    }

    public List<ContentValues> getListNextMeeting(String result)  throws JSONException
    {
        if (result.length() == 0 || result.length() == 3)
            return null;
        JSONObject forecastJSON = new JSONObject(result).getJSONObject("data");
        JSONArray arrayJSON = forecastJSON.getJSONArray("array");
        List<ContentValues> list = new Vector<ContentValues>();
        for (int i = 0; i < arrayJSON.length(); ++i){
            JSONObject obj = arrayJSON.getJSONObject(i);
            ContentValues values = new ContentValues();

            values.put("projects_name", obj.getJSONObject("projects").getString("name"));
            values.put("type", obj.getString("type"));
            values.put("title", obj.getString("title"));
            values.put("description", obj.getString("description"));
            values.put("begin_date", obj.getJSONObject("begin_date").getString("date"));
            values.put("end_date", obj.getJSONObject("end_date").getString("date"));
            list.add(values);
        }
        return list;
    }

    public ContentValues getUserInformation(String result)  throws JSONException {
        final String[] userInfo = {"firstname", "lastname", "birthday", "avatar", "email", "phone", "country", "linkedin", "viadeo", "twitter"};

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

    public List<ContentValues> getMonthPlanning(String resultAPI) throws JSONException
    {
        List<ContentValues> listResult = new Vector<ContentValues>();

        JSONObject forecastJSON = new JSONObject(resultAPI).getJSONObject("data");
        JSONArray arrayJSON = forecastJSON.getJSONObject("array").getJSONArray("events");
        Log.v("JSON ARRAY :", arrayJSON.toString());
        for (int i = 0; i < arrayJSON.length(); ++i)
        {
            JSONObject event = arrayJSON.getJSONObject(i);
            ContentValues values = new ContentValues();

            values.put("id", event.getString("id"));
            values.put("title", event.getString("title"));
            values.put("beginDate", event.getJSONObject("beginDate").getString("date"));
            values.put("endDate", event.getJSONObject("endDate").getString("date"));
            values.put("idTypeEvent", event.getJSONObject("type").getString("id"));
            values.put("nameTypeEvent", event.getJSONObject("type").getString("name"));
            printContentValues(values);
            listResult.add(values);
        }
        return listResult;
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
