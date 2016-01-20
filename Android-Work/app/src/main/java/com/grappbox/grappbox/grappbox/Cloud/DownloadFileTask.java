package com.grappbox.grappbox.grappbox.Cloud;

import android.app.DownloadManager;
import android.content.Context;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Environment;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URISyntaxException;
import java.net.URL;

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
        try {
            URL url = new URL("http://api.grappbox.com/app_dev.php/V0.2/cloud/file/" + cloudPath + "/" + token + "/" + projectId + (safePassword.equals("") ? "" : "/" + safePassword));
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setInstanceFollowRedirects(false);
            connection.getInputStream();
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
