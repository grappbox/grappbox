#include "SFontLoader.h"

#include <QFontDatabase>

SFontLoader::SFontLoader()
{
    _Fonts.insert(SFontLoader::OPEN_SANS_BOLD, QFontDatabase::addApplicationFont(":/Font/Ressources/Font/OpenSans-Bold.ttf"));
}

SFontLoader::~SFontLoader()
{
}

QFont       SFontLoader::GetFont(SFontLoader::Font font)
{
    if (__INSTANCE__SFontLoader == NULL)
        __INSTANCE__SFontLoader = new SFontLoader();
    return QFont(QFontDatabase::applicationFontFamilies(__INSTANCE__SFontLoader->_Fonts[font]).at(0));
}
