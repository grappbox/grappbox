#include <QFile>
#include <QDebug>
#include "SStyleLoader.h"

#define BASE_CSS_PATH "./css/"

static SStyleLoader *__INSTANCE__ = nullptr;

SStyleLoader::SStyleLoader()
{
	_StyleSheets[BASE] = "Base.css";
	_StyleSheets[DASHBOARD] = "Dashboard.css";
	_StyleSheets[WHITEBOARD] = "Whiteboard.css";
	_StyleSheets[CALENDAR] = "Calendar.css";
	_StyleSheets[BUG_TRACKER] = "BugTracker.css";
	_StyleSheets[PROJECT_SETTINGS] = "ProjectSettings.css";
	_StyleSheets[USER_SETTINGS] = "UserSettings.css";
	_StyleSheets[TIMELINE] = "Timeline.css";
	
	for (QMap<STYLE_SHEET, QString>::iterator it = _StyleSheets.begin(); it != _StyleSheets.end(); ++it)
	{
		QFile file(BASE_CSS_PATH + it.value());
		file.open(QFile::ReadOnly);
		if (file.isOpen())
		{
			_StyleSheets[it.key()] = QString(file.readAll());
			file.close();
		}
		else
		{
			qDebug() << "[SStyleLoader] Unable to open style sheet for " << it.value();
		}
	}
}

SStyleLoader::~SStyleLoader()
{
}

QString SStyleLoader::LoadStyleSheet(STYLE_SHEET type)
{
	if (__INSTANCE__ == nullptr)
		__INSTANCE__ = new SStyleLoader();
	return (__INSTANCE__->_StyleSheets[type]);
}