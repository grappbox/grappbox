package com.grappbox.grappbox.grappbox.Cloud;

import android.app.DownloadManager;
import android.content.Context;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Environment;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URISyntaxException;
import java.net.URL;
import java.net.URLEncoder;

/**
 * Created by wieser_m on 20/01/2016.
 */
public class DownloadFileTask extends AsyncTask<String, Void, String> {
    private APIConnectAdapter _api;
    Context _context;

    DownloadFileTask(Context context)
    {
        _context = context;
    }
    @Override
    protected void onPostExecute(String s) {
        super.onPostExecute(s);
    }
    private String getInputStream(HttpURLConnection urlConnection) throws IOException {
        InputStream inputStream = urlConnection.getInputStream();
        StringBuffer buffer = new StringBuffer();
        String res;
        if (inputStream == null) {
            res = "";
        }
        BufferedReader reader = new BufferedReader(new InputStreamReader(inputStream));
        String line;
        while ((line = reader.readLine()) != null) {
            buffer.append(line);
        }

        if (buffer.length() == 0) {
            res = null;
        }
        res = buffer.toString();
        return res;
    }
    @Override
    protected String doInBackground(String... params) {
        if (params.length < 3)
            return null;
        String token = SessionAdapter.getInstance().getToken();
        String projectId = String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject());
        String safePassword = params[1];
        String cloudPath = params[0];
        String filename = params[2];
        _api = APIConnectAdapter.getInstance(true);
        cloudPath = cloudPath.replace("/", ",").replace(" ", "|");
        if (cloudPath.startsWith(",,"))
            cloudPath = cloudPath.substring(1);
        try {
            cloudPath = URLEncoder.encode(cloudPath, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        }
        try {
            URL url = new URL(APIConnectAdapter._APIUrlBase + APIConnectAdapter._APIBaseVersion+"/cloud/file/" + cloudPath + "/" + token + "/" + projectId + (safePassword.equals("") ? "" : "/" + safePassword));
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setInstanceFollowRedirects(false);
            String resultString = getInputStream(connection);
            if (resultString.startsWith("{"))
                Toast.makeText(_context, "The password you tried is incorrect for this file, or you don't have the rights to access to this part", Toast.LENGTH_LONG).show();
            url = connection.getURL();
            DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url.toURI().toString()));
            request.allowScanningByMediaScanner();
            request.setTitle(filename);
            request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE);
            request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, filename);
            DownloadManager manager = (DownloadManager) _context.getSystemService(Context.DOWNLOAD_SERVICE);
            manager.enqueue(request);
        } catch (IOException | URISyntaxException e) {
            e.printStackTrace();
        }
        return null;
    }
}
