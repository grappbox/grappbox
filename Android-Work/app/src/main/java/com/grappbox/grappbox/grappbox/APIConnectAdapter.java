package com.grappbox.grappbox.grappbox;

import android.content.ContentValues;
import android.util.Log;

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

/**
 * Created by Arkanice on 08/12/2015.
 */
public class APIConnectAdapter  {

    private static APIConnectAdapter _instance = null;

    private static final String _APIUrlBase = "http://api.grappbox.com/app_dev.php/";
    private static final String _APIVersion = "V0.8";
    HttpURLConnection _connection = null;
    BufferedReader reader = null;
    String resultAPI;
    URL _url;
    DataOutputStream _dataOutputStream;

    private APIConnectAdapter() {

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
        _url = new URL(connectURL);
        _connection = (HttpURLConnection) _url.openConnection();
        _connection.setReadTimeout(10000);
        _connection.setConnectTimeout(15000);
    }

    public void setRequestConnection(String typeRequest) throws ProtocolException
    {
        _connection.setRequestMethod(typeRequest);
    }

    public void sendJSON(JSONObject JSONParam) throws IOException
    {
        _dataOutputStream = new DataOutputStream(_connection.getOutputStream());
        _dataOutputStream.write(JSONParam.toString().getBytes("UTF-8"));
        _dataOutputStream.flush();
        _dataOutputStream.close();
        _connection.connect();
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
        }
        if (reader != null){
            try {
                reader.close();
            } catch (final IOException e){
                Log.e("APIConnection", "Error ", e);
            }
        }
    }
}
