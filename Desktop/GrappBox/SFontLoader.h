#ifndef SFONTLOADER_H
#define SFONTLOADER_H

#include <QMap>
#include <QFont>

class SFontLoader
{
public:
    enum Font
    {
        OPEN_SANS_BOLD
    };

private:
    SFontLoader();
    ~SFontLoader();

public:
    static QFont GetFont(SFontLoader::Font font);

private:
    QMap<SFontLoader::Font, int>      _Fonts;
};

static SFontLoader *__INSTANCE__SFontLoader = nullptr;

#endif // SFONTLOADER_H
